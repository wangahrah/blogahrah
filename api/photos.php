<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

define('PHOTOS_FILE', __DIR__ . '/../photos/photos.json');

// GET - List all photos (filter private photos for unauthenticated users)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $isAuthenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'];

    if (!file_exists(PHOTOS_FILE)) {
        echo json_encode([]);
        exit;
    }

    $photos = json_decode(file_get_contents(PHOTOS_FILE), true) ?: [];

    // Filter private photos for unauthenticated users
    if (!$isAuthenticated) {
        $photos = array_values(array_filter($photos, fn($p) => empty($p['private'])));
    }

    // Sort by order, then by date descending
    usort($photos, function($a, $b) {
        $orderA = $a['order'] ?? 999;
        $orderB = $b['order'] ?? 999;
        if ($orderA !== $orderB) return $orderA - $orderB;
        return strcmp($b['date'] ?? '', $a['date'] ?? '');
    });

    echo json_encode($photos);
    exit;
}

// POST - Create new photo (authenticated only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $input = json_decode(file_get_contents('php://input'), true);
    $image = $input['image'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $date = $input['date'] ?? date('Y-m-d');
    $order = isset($input['order']) ? (int)$input['order'] : 0;
    $private = isset($input['private']) && $input['private'] === true;

    if (empty($image)) {
        http_response_code(400);
        echo json_encode(['error' => 'Image required']);
        exit;
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        exit;
    }

    // Validate image path (must be /media/ path)
    if (!preg_match('/^\/media\/[a-zA-Z0-9._-]+$/', $image)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid image path']);
        exit;
    }

    // Validate title length
    if (strlen($title) > 200) {
        http_response_code(400);
        echo json_encode(['error' => 'Title too long (max 200 characters)']);
        exit;
    }

    // Load existing photos
    $photos = [];
    if (file_exists(PHOTOS_FILE)) {
        $photos = json_decode(file_get_contents(PHOTOS_FILE), true) ?: [];
    }

    // Create new photo entry
    $id = time() . '-' . bin2hex(random_bytes(4));
    $photo = [
        'id' => $id,
        'image' => $image,
        'title' => $title,
        'description' => $description,
        'date' => $date,
        'order' => $order,
        'private' => $private
    ];

    $photos[] = $photo;

    // Ensure directory exists
    $dir = dirname(PHOTOS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (file_put_contents(PHOTOS_FILE, json_encode($photos, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save photo']);
    }
    exit;
}

// PUT - Update existing photo (authenticated only)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
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

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';
    $image = $input['image'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $date = $input['date'] ?? '';
    $order = isset($input['order']) ? (int)$input['order'] : 0;
    $private = isset($input['private']) && $input['private'] === true;

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Photo ID required']);
        exit;
    }

    if (empty($image)) {
        http_response_code(400);
        echo json_encode(['error' => 'Image required']);
        exit;
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        exit;
    }

    // Validate image path
    if (!preg_match('/^\/media\/[a-zA-Z0-9._-]+$/', $image)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid image path']);
        exit;
    }

    // Load existing photos
    if (!file_exists(PHOTOS_FILE)) {
        http_response_code(404);
        echo json_encode(['error' => 'Photo not found']);
        exit;
    }

    $photos = json_decode(file_get_contents(PHOTOS_FILE), true) ?: [];
    $found = false;

    foreach ($photos as &$photo) {
        if ($photo['id'] === $id) {
            $photo['image'] = $image;
            $photo['title'] = $title;
            $photo['description'] = $description;
            $photo['date'] = $date;
            $photo['order'] = $order;
            $photo['private'] = $private;
            $found = true;
            break;
        }
    }

    if (!$found) {
        http_response_code(404);
        echo json_encode(['error' => 'Photo not found']);
        exit;
    }

    if (file_put_contents(PHOTOS_FILE, json_encode($photos, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update photo']);
    }
    exit;
}

// DELETE - Delete existing photo (authenticated only)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
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

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Photo ID required']);
        exit;
    }

    // Validate ID format to prevent injection
    if (!preg_match('/^[a-zA-Z0-9\-]+$/', $id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID format']);
        exit;
    }

    // Load existing photos
    if (!file_exists(PHOTOS_FILE)) {
        http_response_code(404);
        echo json_encode(['error' => 'Photo not found']);
        exit;
    }

    $photos = json_decode(file_get_contents(PHOTOS_FILE), true) ?: [];
    $originalCount = count($photos);
    $photos = array_values(array_filter($photos, fn($p) => $p['id'] !== $id));

    if (count($photos) === $originalCount) {
        http_response_code(404);
        echo json_encode(['error' => 'Photo not found']);
        exit;
    }

    if (file_put_contents(PHOTOS_FILE, json_encode($photos, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete photo']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
