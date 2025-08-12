<?php
require_once 'config/database.php';

// Get image ID from URL
$imageId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($imageId <= 0) {
    http_response_code(400);
    die('Invalid image ID');
}

try {
    $db = Database::getInstance();
    
    // Get image information with user data
    $sql = "SELECT i.*, u.name as user_name, u.avatar as user_avatar,
                   (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count,
                   (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments_count
            FROM images i 
            LEFT JOIN users u ON i.user_id = u.id 
            WHERE i.id = ? AND i.is_public = 1";
    
    $stmt = $db->query($sql, [$imageId]);
    $image = $stmt->fetch();
    
    if (!$image) {
        http_response_code(404);
        die('Image not found');
    }
    
    // Increment view count
    $updateSql = "UPDATE images SET views = views + 1 WHERE id = ?";
    $db->query($updateSql, [$imageId]);
    
} catch (Exception $e) {
    http_response_code(500);
    die('Server error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($image['title']); ?> - PhotoShare</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .image-viewer {
            min-height: 100vh;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .image-container {
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
        }
        
        .image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 12px;
        }
        
        .image-info {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 12px;
            max-width: 300px;
            backdrop-filter: blur(10px);
        }
        
        .image-actions {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 1rem;
            background: rgba(0, 0, 0, 0.8);
            padding: 15px 25px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }
        
        .close-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .close-btn:hover {
            background: rgba(255, 0, 0, 0.8);
            transform: scale(1.1);
        }
        
        .stats {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .image-info {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                max-width: 100%;
            }
            
            .image-actions {
                position: relative;
                bottom: auto;
                left: auto;
                transform: none;
                margin-top: 20px;
                justify-content: center;
            }
            
            .close-btn {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
            }
            
            .image-viewer {
                flex-direction: column;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="image-viewer">
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" 
                 alt="<?php echo htmlspecialchars($image['title']); ?>"
                 loading="lazy">
        </div>
        
        <div class="image-info">
            <h2><?php echo htmlspecialchars($image['title']); ?></h2>
            <p><strong>Автор:</strong> <?php echo htmlspecialchars($image['user_name'] ?? 'Anonymous'); ?></p>
            <p><strong>Категория:</strong> <?php echo htmlspecialchars($image['category']); ?></p>
            <p><strong>Размер:</strong> <?php echo htmlspecialchars($image['file_size']); ?></p>
            <p><strong>Загружено:</strong> <?php echo date('d.m.Y H:i', strtotime($image['created_at'])); ?></p>
            
            <div class="stats">
                <div class="stat-item">
                    <i class="fas fa-eye"></i>
                    <span><?php echo number_format($image['views']); ?></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-download"></i>
                    <span><?php echo number_format($image['downloads']); ?></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-heart"></i>
                    <span><?php echo number_format($image['likes_count']); ?></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-comment"></i>
                    <span><?php echo number_format($image['comments_count']); ?></span>
                </div>
            </div>
            
            <?php if ($image['description']): ?>
                <p><strong>Описание:</strong> <?php echo htmlspecialchars($image['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="image-actions">
            <a href="download.php?id=<?php echo $image['id']; ?>" class="btn btn-primary">
                <i class="fas fa-download"></i> Скачать
            </a>
            <button class="btn btn-outline" onclick="shareImage()">
                <i class="fas fa-share-alt"></i> Поделиться
            </button>
            <button class="btn btn-secondary" onclick="likeImage()">
                <i class="fas fa-heart"></i> Нравится
            </button>
        </div>
        
        <button class="close-btn" onclick="goBack()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <script>
        function goBack() {
            if (document.referrer) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }
        
        function shareImage() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($image['title']); ?>',
                    url: window.location.href
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Ссылка скопирована в буфер обмена!');
                });
            }
        }
        
        function likeImage() {
            // TODO: Implement like functionality
            alert('Функция лайков будет добавлена в следующем обновлении!');
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'Escape':
                    goBack();
                    break;
                case 'ArrowLeft':
                    // TODO: Previous image
                    break;
                case 'ArrowRight':
                    // TODO: Next image
                    break;
            }
        });
        
        // Prevent context menu on image
        document.querySelector('.image-container img').addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>