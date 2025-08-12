<?php
require_once 'config/database.php';

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    http_response_code(400);
    die('Invalid user ID');
}

$user = null;
$userImages = [];
$userStats = [];

try {
    $db = Database::getInstance();
    
    // Get user information
    $userSql = "SELECT id, name, email, avatar, created_at FROM users WHERE id = ?";
    $userStmt = $db->query($userSql, [$userId]);
    $user = $userStmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        die('User not found');
    }
    
    // Get user images
    $imagesSql = "SELECT i.*, 
                         (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count,
                         (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments_count
                  FROM images i 
                  WHERE i.user_id = ? AND i.is_public = 1 
                  ORDER BY i.created_at DESC 
                  LIMIT 12";
    
    $imagesStmt = $db->query($imagesSql, [$userId]);
    $userImages = $imagesStmt->fetchAll();
    
    // Get user statistics
    $statsSql = "SELECT 
                     COUNT(*) as total_images,
                     SUM(views) as total_views,
                     SUM(downloads) as total_downloads
                  FROM images 
                  WHERE user_id = ? AND is_public = 1";
    
    $statsStmt = $db->query($statsSql, [$userId]);
    $userStats = $statsStmt->fetch();
    
    // Get total likes received
    $likesSql = "SELECT COUNT(*) as total_likes 
                  FROM likes l 
                  JOIN images i ON l.image_id = i.id 
                  WHERE i.user_id = ? AND i.is_public = 1";
    
    $likesStmt = $db->query($likesSql, [$userId]);
    $totalLikes = $likesStmt->fetch()['total_likes'];
    
} catch (Exception $e) {
    $error = 'Ошибка загрузки профиля: ' . $e->getMessage();
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
    <title>Профиль <?php echo htmlspecialchars($user['name']); ?> - PhotoShare</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-page {
            padding: 120px 0 80px;
            min-height: 100vh;
            background: var(--bg-secondary);
        }
        
        .profile-header {
            background: white;
            padding: 3rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin: 0 1rem 3rem;
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
            font-weight: 700;
        }
        
        .profile-name {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .profile-email {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
        }
        
        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .profile-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .content-section {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: var(--primary-color);
        }
        
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
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
        
        .profile-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                margin: 0 0.5rem 2rem;
                padding: 2rem 1rem;
            }
            
            .profile-name {
                font-size: 2rem;
            }
            
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .stat-item {
                padding: 0.75rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .profile-actions {
                flex-direction: column;
                align-items: center;
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

    <section class="profile-page">
        <div class="container">
            <div class="breadcrumb">
                <a href="/">Главная</a>
                <i class="fas fa-chevron-right"></i>
                <a href="/#gallery">Галерея</a>
                <i class="fas fa-chevron-right"></i>
                <span>Профиль <?php echo htmlspecialchars($user['name']); ?></span>
            </div>

            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if ($user['avatar']): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                             alt="<?php echo htmlspecialchars($user['name']); ?>"
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                
                <h1 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p>Участник с <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></p>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($userStats['total_images']); ?></span>
                        <span class="stat-label">Изображений</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($userStats['total_views']); ?></span>
                        <span class="stat-label">Просмотров</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($userStats['total_downloads']); ?></span>
                        <span class="stat-label">Скачиваний</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($totalLikes); ?></span>
                        <span class="stat-label">Лайков</span>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <a href="/#upload" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Загрузить изображение
                    </a>
                    <a href="/search.php" class="btn btn-outline">
                        <i class="fas fa-search"></i>
                        Поиск изображений
                    </a>
                </div>
            </div>

            <div class="profile-content">
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-images"></i>
                        Изображения пользователя
                    </h2>
                    
                    <?php if (empty($userImages)): ?>
                        <div class="no-images">
                            <i class="fas fa-images"></i>
                            <h3>У пользователя пока нет изображений</h3>
                            <p>Этот пользователь еще не загрузил ни одной фотографии</p>
                        </div>
                    <?php else: ?>
                        <div class="images-grid">
                            <?php foreach ($userImages as $image): ?>
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
                                            <span><i class="fas fa-eye"></i> <?php echo number_format($image['views']); ?></span>
                                            <span><i class="fas fa-download"></i> <?php echo number_format($image['downloads']); ?></span>
                                            <span><i class="fas fa-heart"></i> <?php echo number_format($image['likes_count']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($userImages) >= 12): ?>
                            <div style="text-align: center; margin-top: 2rem;">
                                <a href="/search.php?user=<?php echo $userId; ?>" class="btn btn-outline">
                                    <i class="fas fa-plus"></i>
                                    Показать все изображения
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-chart-line"></i>
                        Статистика активности
                    </h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: var(--bg-secondary); border-radius: var(--border-radius);">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                <?php echo $userStats['total_images'] > 0 ? round($userStats['total_views'] / $userStats['total_images']) : 0; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: var(--text-light);">Среднее просмотров на изображение</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: var(--bg-secondary); border-radius: var(--border-radius);">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">
                                <?php echo $userStats['total_images'] > 0 ? round($userStats['total_downloads'] / $userStats['total_images'] * 100, 1) : 0; ?>%
                            </div>
                            <div style="font-size: 0.875rem; color: var(--text-light);">Процент скачиваний</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: var(--bg-secondary); border-radius: var(--border-radius);">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--secondary-color);">
                                <?php echo $userStats['total_images'] > 0 ? round($totalLikes / $userStats['total_images'], 1) : 0; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: var(--text-light);">Среднее лайков на изображение</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
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
        
        // Animate stats on scroll
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>