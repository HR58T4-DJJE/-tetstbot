<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

class ImageGallery {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getImages($page = 1, $limit = 12, $category = null, $search = null) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = ['is_public = 1'];
            $params = [];
            
            if ($category && $category !== 'all') {
                $whereConditions[] = 'category = ?';
                $params[] = $category;
            }
            
            if ($search) {
                $whereConditions[] = '(title LIKE ? OR description LIKE ?)';
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM images WHERE $whereClause";
            $countStmt = $this->db->query($countSql, $params);
            $totalCount = $countStmt->fetch()['total'];
            
            // Get images
            $sql = "SELECT i.*, u.name as user_name, u.avatar as user_avatar,
                           (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count,
                           (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments_count
                    FROM images i 
                    LEFT JOIN users u ON i.user_id = u.id 
                    WHERE $whereClause 
                    ORDER BY i.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->query($sql, $params);
            $images = $stmt->fetchAll();
            
            // Process images
            $processedImages = array_map([$this, 'processImage'], $images);
            
            return [
                'success' => true,
                'data' => [
                    'images' => $processedImages,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => ceil($totalCount / $limit),
                        'total_count' => $totalCount,
                        'per_page' => $limit
                    ]
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch images: ' . $e->getMessage()
            ];
        }
    }

    public function getImageById($id) {
        try {
            $sql = "SELECT i.*, u.name as user_name, u.avatar as user_avatar,
                           (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count,
                           (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments_count
                    FROM images i 
                    LEFT JOIN users u ON i.user_id = u.id 
                    WHERE i.id = ? AND i.is_public = 1";
            
            $stmt = $this->db->query($sql, [$id]);
            $image = $stmt->fetch();
            
            if (!$image) {
                return [
                    'success' => false,
                    'message' => 'Image not found'
                ];
            }
            
            // Increment view count
            $this->incrementViewCount($id);
            
            return [
                'success' => true,
                'data' => $this->processImage($image)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch image: ' . $e->getMessage()
            ];
        }
    }

    public function getCategories() {
        try {
            $sql = "SELECT c.*, COUNT(i.id) as image_count 
                    FROM categories c 
                    LEFT JOIN images i ON c.slug = i.category AND i.is_public = 1
                    GROUP BY c.id 
                    ORDER BY image_count DESC, c.name ASC";
            
            $stmt = $this->db->query($sql);
            $categories = $stmt->fetchAll();
            
            return [
                'success' => true,
                'data' => $categories
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch categories: ' . $e->getMessage()
            ];
        }
    }

    public function getRandomImages($limit = 6) {
        try {
            $sql = "SELECT i.*, u.name as user_name, u.avatar as user_avatar,
                           (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count
                    FROM images i 
                    LEFT JOIN users u ON i.user_id = u.id 
                    WHERE i.is_public = 1 
                    ORDER BY RAND() 
                    LIMIT ?";
            
            $stmt = $this->db->query($sql, [$limit]);
            $images = $stmt->fetchAll();
            
            $processedImages = array_map([$this, 'processImage'], $images);
            
            return [
                'success' => true,
                'data' => $processedImages
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch random images: ' . $e->getMessage()
            ];
        }
    }

    public function getTrendingImages($limit = 8) {
        try {
            $sql = "SELECT i.*, u.name as user_name, u.avatar as user_avatar,
                           (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes_count,
                           (i.views + i.downloads) as popularity_score
                    FROM images i 
                    LEFT JOIN users u ON i.user_id = u.id 
                    WHERE i.is_public = 1 
                    ORDER BY popularity_score DESC, i.created_at DESC 
                    LIMIT ?";
            
            $stmt = $this->db->query($sql, [$limit]);
            $images = $stmt->fetchAll();
            
            $processedImages = array_map([$this, 'processImage'], $images);
            
            return [
                'success' => true,
                'data' => $processedImages
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch trending images: ' . $e->getMessage()
            ];
        }
    }

    private function processImage($image) {
        return [
            'id' => $image['id'],
            'title' => $image['title'],
            'filename' => $image['filename'],
            'original_name' => $image['original_name'],
            'url' => 'uploads/' . $image['filename'],
            'thumbnail_url' => 'uploads/thumbnails/' . $image['filename'],
            'file_size' => $this->formatFileSize($image['file_size']),
            'mime_type' => $image['mime_type'],
            'category' => $image['category'],
            'description' => $image['description'],
            'views' => $image['views'],
            'downloads' => $image['downloads'],
            'likes_count' => $image['likes_count'] ?? 0,
            'comments_count' => $image['comments_count'] ?? 0,
            'user_name' => $image['user_name'] ?? 'Anonymous',
            'user_avatar' => $image['user_avatar'],
            'created_at' => $image['created_at'],
            'formatted_date' => $this->formatDate($image['created_at'])
        ];
    }

    private function incrementViewCount($imageId) {
        $sql = "UPDATE images SET views = views + 1 WHERE id = ?";
        $this->db->query($sql, [$imageId]);
    }

    private function formatFileSize($bytes) {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    private function formatDate($dateString) {
        $date = new DateTime($dateString);
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        } elseif ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        } elseif ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }
}

// Handle requests
$gallery = new ImageGallery();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 12);
            $category = $_GET['category'] ?? null;
            $search = $_GET['search'] ?? null;
            
            $result = $gallery->getImages($page, $limit, $category, $search);
            break;
            
        case 'single':
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $result = $gallery->getImageById($id);
            } else {
                $result = ['success' => false, 'message' => 'Invalid image ID'];
            }
            break;
            
        case 'categories':
            $result = $gallery->getCategories();
            break;
            
        case 'random':
            $limit = intval($_GET['limit'] ?? 6);
            $result = $gallery->getRandomImages($limit);
            break;
            
        case 'trending':
            $limit = intval($_GET['limit'] ?? 8);
            $result = $gallery->getTrendingImages($limit);
            break;
            
        default:
            $result = ['success' => false, 'message' => 'Invalid action'];
    }
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>