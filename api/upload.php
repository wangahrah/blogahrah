<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Must be authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Validate CSRF token
$csrfToken = getCsrfTokenFromRequest();
if (!validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Check if file uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['file'];

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if ($finfo === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error']);
    exit;
}
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!array_key_exists($mimeType, ALLOWED_TYPES)) {
    http_response_code(400);
    echo json_encode(['error' => 'File type not allowed']);
    exit;
}

// Get and validate extension
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts = ALLOWED_TYPES[$mimeType];

if (!in_array($ext, $allowedExts)) {
    http_response_code(400);
    echo json_encode(['error' => 'File extension does not match content type']);
    exit;
}

// Validate file size - allow up to 50MB for images (will be resized), 5MB for videos
$isImage = str_starts_with($mimeType, 'image/');
$uploadLimit = $isImage ? 50 * 1024 * 1024 : MAX_FILE_SIZE;
if ($file['size'] > $uploadLimit) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large (max ' . ($isImage ? '50MB' : '5MB') . ')']);
    exit;
}

// Create media directory if needed
if (!is_dir(MEDIA_DIR)) {
    mkdir(MEDIA_DIR, 0755, true);
}

// Generate unique filename with validated extension
$safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
$safeName = substr($safeName, 0, 50); // Limit filename length
if (empty($safeName)) {
    $safeName = 'upload';
}
$filename = date('Y-m-d') . '-' . $safeName . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$filepath = MEDIA_DIR . '/' . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Auto-resize images if over 2MB to ensure under 5MB final size
    if (str_starts_with($mimeType, 'image/') && filesize($filepath) > 2 * 1024 * 1024) {
        $resized = resizeImageToFitSize($filepath, $mimeType, MAX_FILE_SIZE);
        if (!$resized) {
            unlink($filepath);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to resize image']);
            exit;
        }
    }

    $url = '/media/' . $filename;

    // Return markdown-appropriate embed
    $isVideo = str_starts_with($mimeType, 'video/');
    if ($isVideo) {
        $markdown = '<video src="' . $url . '" controls></video>';
    } else {
        $markdown = '![' . $safeName . '](' . $url . ')';
    }

    echo json_encode([
        'success' => true,
        'url' => $url,
        'filename' => $filename,
        'markdown' => $markdown
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
}

/**
 * Resize image to fit under max file size
 */
function resizeImageToFitSize($filepath, $mimeType, $maxSize) {
    // Load image based on type
    switch ($mimeType) {
        case 'image/jpeg':
            $img = imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $img = imagecreatefrompng($filepath);
            break;
        case 'image/gif':
            $img = imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            $img = imagecreatefromwebp($filepath);
            break;
        default:
            return false;
    }

    if (!$img) return false;

    $width = imagesx($img);
    $height = imagesy($img);
    $quality = 85;

    // Try progressively smaller sizes until under limit
    for ($scale = 1.0; $scale >= 0.2; $scale -= 0.1) {
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG/GIF/WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/gif' || $mimeType === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
        }

        imagecopyresampled($resized, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save to temp file to check size
        $tempFile = $filepath . '.tmp';

        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($resized, $tempFile, $quality);
                break;
            case 'image/png':
                imagepng($resized, $tempFile, 6);
                break;
            case 'image/gif':
                imagegif($resized, $tempFile);
                break;
            case 'image/webp':
                imagewebp($resized, $tempFile, $quality);
                break;
        }

        imagedestroy($resized);

        if (filesize($tempFile) <= $maxSize) {
            rename($tempFile, $filepath);
            imagedestroy($img);
            return true;
        }

        unlink($tempFile);

        // For JPEG/WebP, also try reducing quality
        if (($mimeType === 'image/jpeg' || $mimeType === 'image/webp') && $quality > 50) {
            $quality -= 10;
            $scale += 0.1; // Retry same scale with lower quality
        }
    }

    imagedestroy($img);
    return false;
}
