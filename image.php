<?php
require 'config/database.php';

// Check if ID parameter exists
if (!isset($_GET['id'])) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$item_id = intval($_GET['id']);

// Get image data from database
$stmt = $conn->prepare("SELECT image, name FROM items WHERE id = :id");
$stmt->execute([':id' => $item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item || empty($item['image'])) {
    // Return placeholder image
    $placeholderPath = 'assets/images/placeholder.png';
    if (file_exists($placeholderPath)) {
        $contentType = 'image/png';
        $imageData = file_get_contents($placeholderPath);
    } else {
        header("HTTP/1.0 404 Not Found");
        exit;
    }
} else {
    $imagePath = 'assets/' . $item['image'];
    
    if (file_exists($imagePath)) {
        // Determine content type based on file extension
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                $contentType = 'image/jpeg';
                break;
            case 'png':
                $contentType = 'image/png';
                break;
            case 'gif':
                $contentType = 'image/gif';
                break;
            case 'webp':
                $contentType = 'image/webp';
                break;
            default:
                $contentType = 'application/octet-stream';
        }
        
        $imageData = file_get_contents($imagePath);
    } else {
        // Image file not found, return placeholder
        $placeholderPath = 'assets/images/placeholder.png';
        if (file_exists($placeholderPath)) {
            $contentType = 'image/png';
            $imageData = file_get_contents($placeholderPath);
        } else {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
    }
}

// Output image with correct headers
header("Content-Type: $contentType");
header("Content-Length: " . strlen($imageData));
header("Cache-Control: max-age=2592000"); // Cache for 30 days
echo $imageData;
exit;