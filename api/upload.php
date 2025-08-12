<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

class ImageUploader {
    private $db;
    private $uploadDir = '../uploads/';
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB

    public function __construct() {
        $this->db = Database::getInstance();
        $this->createUploadDirectory();
    }

    private function createUploadDirectory() {
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function uploadImage($file, $userId = null) {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filePath = $this->uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Failed to move uploaded file');
            }

            // Get image info
            $imageInfo = getimagesize($filePath);
            if (!$imageInfo) {
                throw new Exception('Invalid image file');
            }

            // Create thumbnail
            $thumbnailPath = $this->createThumbnail($filePath, $filename);

            // Save to database
            $imageId = $this->saveImageToDatabase($file, $filename, $filePath, $thumbnailPath, $userId);

            // Return success response
            return [
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'id' => $imageId,
                    'filename' => $filename,
                    'original_name' => $file['name'],
                    'size' => $this->formatFileSize($file['size']),
                    'url' => 'uploads/' . $filename,
                    'thumbnail_url' => 'uploads/thumbnails/' . $filename
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $file['error']);
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds limit of ' . $this->formatFileSize($this->maxFileSize));
        }

        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
        }

        // Check if file is actually an image
        if (!getimagesize($file['tmp_name'])) {
            throw new Exception('File is not a valid image');
        }
    }

    private function createThumbnail($sourcePath, $filename) {
        $thumbnailDir = $this->uploadDir . 'thumbnails/';
        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $thumbnailPath = $thumbnailDir . $filename;
        $maxWidth = 300;
        $maxHeight = 300;

        list($width, $height) = getimagesize($sourcePath);
        
        // Calculate new dimensions
        if ($width > $height) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
        } else {
            $newHeight = $maxHeight;
            $newWidth = floor($width * ($maxHeight / $height));
        }

        // Create image resource
        $sourceImage = imagecreatefromstring(file_get_contents($sourcePath));
        $thumbnailImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        imagealphablending($thumbnailImage, false);
        imagesavealpha($thumbnailImage, true);

        // Resize image
        imagecopyresampled($thumbnailImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save thumbnail
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($thumbnailImage, $thumbnailPath, 85);
                break;
            case 'png':
                imagepng($thumbnailImage, $thumbnailPath, 8);
                break;
            case 'gif':
                imagegif($thumbnailImage, $thumbnailPath);
                break;
            case 'webp':
                imagewebp($thumbnailImage, $thumbnailPath, 85);
                break;
        }

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbnailImage);

        return $thumbnailPath;
    }

    private function saveImageToDatabase($file, $filename, $filePath, $thumbnailPath, $userId) {
        $sql = "INSERT INTO images (user_id, title, filename, original_name, file_path, file_size, mime_type, category) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $title = pathinfo($file['name'], PATHINFO_FILENAME);
        $category = $this->detectCategory($file['name']);
        
        $this->db->query($sql, [
            $userId,
            $title,
            $filename,
            $file['name'],
            $filePath,
            $file['size'],
            $file['type'],
            $category
        ]);

        return $this->db->lastInsertId();
    }

    private function detectCategory($filename) {
        $name = strtolower($filename);
        
        if (strpos($name, 'nature') !== false || strpos($name, 'landscape') !== false || strpos($name, 'forest') !== false) {
            return 'nature';
        }
        if (strpos($name, 'city') !== false || strpos($name, 'urban') !== false || strpos($name, 'street') !== false) {
            return 'city';
        }
        if (strpos($name, 'portrait') !== false || strpos($name, 'person') !== false || strpos($name, 'face') !== false) {
            return 'portrait';
        }
        if (strpos($name, 'architecture') !== false || strpos($name, 'building') !== false) {
            return 'architecture';
        }
        if (strpos($name, 'travel') !== false || strpos($name, 'country') !== false) {
            return 'travel';
        }
        if (strpos($name, 'food') !== false || strpos($name, 'restaurant') !== false) {
            return 'food';
        }
        if (strpos($name, 'sport') !== false || strpos($name, 'fitness') !== false) {
            return 'sport';
        }
        if (strpos($name, 'art') !== false || strpos($name, 'creative') !== false) {
            return 'art';
        }
        
        return 'other';
    }

    private function formatFileSize($bytes) {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}

// Handle upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No image file provided'
        ]);
        exit;
    }

    $uploader = new ImageUploader();
    $result = $uploader->uploadImage($_FILES['image']);
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>