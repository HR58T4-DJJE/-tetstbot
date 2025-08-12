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
    
    // Get image information
    $sql = "SELECT * FROM images WHERE id = ? AND is_public = 1";
    $stmt = $db->query($sql, [$imageId]);
    $image = $stmt->fetch();
    
    if (!$image) {
        http_response_code(404);
        die('Image not found');
    }
    
    $filePath = $image['file_path'];
    
    // Check if file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('File not found');
    }
    
    // Increment download count
    $updateSql = "UPDATE images SET downloads = downloads + 1 WHERE id = ?";
    $db->query($updateSql, [$imageId]);
    
    // Get file info
    $fileSize = filesize($filePath);
    $mimeType = mime_content_type($filePath);
    
    // Set headers for download
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $image['original_name'] . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output file
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    die('Server error: ' . $e->getMessage());
}
?>