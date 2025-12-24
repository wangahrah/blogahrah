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

// Check if file uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['file'];

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, ALLOWED_TYPES)) {
    http_response_code(400);
    echo json_encode(['error' => 'File type not allowed: ' . $mimeType]);
    exit;
}

// Validate file size
if ($file['size'] > MAX_FILE_SIZE) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large (max 50MB)']);
    exit;
}

// Create media directory if needed
if (!is_dir(MEDIA_DIR)) {
    mkdir(MEDIA_DIR, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
$filename = date('Y-m-d') . '-' . $safeName . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$filepath = MEDIA_DIR . '/' . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
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
