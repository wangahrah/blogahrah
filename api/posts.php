<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// GET - List all posts (filter private posts for unauthenticated users)
// Supports ?slug=X to fetch a single post (including private) by slug
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $isAuthenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'];

    if (!is_dir(BLOGS_DIR)) {
        echo json_encode([]);
        exit;
    }

    // Single post fetch by slug — returns the post even if private
    $requestedSlug = $_GET['slug'] ?? null;
    if ($requestedSlug !== null) {
        // Validate slug format to prevent path traversal
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $requestedSlug)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid slug format']);
            exit;
        }

        $filepath = BLOGS_DIR . '/' . $requestedSlug . '.md';
        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['error' => 'Post not found']);
            exit;
        }

        $content = file_get_contents($filepath);
        $meta = parseFromtmatter($content);
        $filename = basename($filepath);
        $isPrivate = isset($meta['private']) && ($meta['private'] === 'true' || $meta['private'] === true);

        $post = [
            'filename' => $filename,
            'slug' => pathinfo($filename, PATHINFO_FILENAME),
            'title' => $meta['title'] ?? 'Untitled',
            'date' => $meta['date'] ?? substr($filename, 0, 10),
            'content' => $meta['content'],
            'private' => $isPrivate
        ];

        // Tell search engines not to index private posts
        if ($isPrivate) {
            header('X-Robots-Tag: noindex, nofollow');
        }

        echo json_encode($post);
        exit;
    }

    // List all posts
    $posts = [];
    $files = glob(BLOGS_DIR . '/*.md');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $meta = parseFromtmatter($content);
        $filename = basename($file);

        // Check if post is private
        $isPrivate = isset($meta['private']) && ($meta['private'] === 'true' || $meta['private'] === true);

        // Skip private posts for unauthenticated users
        if ($isPrivate && !$isAuthenticated) {
            continue;
        }

        $posts[] = [
            'filename' => $filename,
            'slug' => pathinfo($filename, PATHINFO_FILENAME),
            'title' => $meta['title'] ?? 'Untitled',
            'date' => $meta['date'] ?? substr($filename, 0, 10),
            'content' => $meta['content'],
            'private' => $isPrivate
        ];
    }

    // Sort by date descending
    usort($posts, fn($a, $b) => strcmp($b['date'], $a['date']));

    echo json_encode($posts);
    exit;
}

// POST - Create new post (authenticated only)
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
    $title = $input['title'] ?? '';
    $content = $input['content'] ?? '';
    $date = $input['date'] ?? date('Y-m-d');
    $private = isset($input['private']) && $input['private'] === true;

    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and content required']);
        exit;
    }

    // Validate date format strictly to prevent path traversal
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        exit;
    }

    // Validate title length
    if (strlen($title) > 200) {
        http_response_code(400);
        echo json_encode(['error' => 'Title too long (max 200 characters)']);
        exit;
    }

    // Create slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    $slug = preg_replace('/-+/', '-', $slug); // Collapse multiple dashes
    $filename = $date . '-' . $slug . '.md';
    $filepath = BLOGS_DIR . '/' . $filename;

    // Don't overwrite existing posts
    if (file_exists($filepath)) {
        http_response_code(409);
        echo json_encode(['error' => 'Post with this title and date already exists']);
        exit;
    }

    // Create markdown with frontmatter (escape special YAML characters)
    $safeTitle = str_replace([':', '#', '>', '|', '\n'], ['：', '＃', '＞', '｜', ' '], $title);
    $markdown = "---\n";
    $markdown .= "title: \"" . addslashes($safeTitle) . "\"\n";
    $markdown .= "date: " . $date . "\n";
    $markdown .= "private: " . ($private ? 'true' : 'false') . "\n";
    $markdown .= "---\n\n";
    $markdown .= $content;

    if (!is_dir(BLOGS_DIR)) {
        mkdir(BLOGS_DIR, 0755, true);
    }

    if (file_put_contents($filepath, $markdown)) {
        echo json_encode(['success' => true, 'filename' => $filename, 'slug' => pathinfo($filename, PATHINFO_FILENAME)]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save post']);
    }
    exit;
}

// PUT - Update existing post (authenticated only)
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
    $slug = $input['slug'] ?? '';
    $title = $input['title'] ?? '';
    $content = $input['content'] ?? '';
    $date = $input['date'] ?? '';
    $private = isset($input['private']) && $input['private'] === true;

    if (empty($slug) || empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug, title and content required']);
        exit;
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        exit;
    }

    // Validate title length
    if (strlen($title) > 200) {
        http_response_code(400);
        echo json_encode(['error' => 'Title too long (max 200 characters)']);
        exit;
    }

    // Find existing file by slug
    $filepath = BLOGS_DIR . '/' . $slug . '.md';
    if (!file_exists($filepath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    // Create updated markdown with frontmatter
    $safeTitle = str_replace([':', '#', '>', '|', '\n'], ['：', '＃', '＞', '｜', ' '], $title);
    $markdown = "---\n";
    $markdown .= "title: \"" . addslashes($safeTitle) . "\"\n";
    $markdown .= "date: " . $date . "\n";
    $markdown .= "private: " . ($private ? 'true' : 'false') . "\n";
    $markdown .= "---\n\n";
    $markdown .= $content;

    if (file_put_contents($filepath, $markdown)) {
        echo json_encode(['success' => true, 'slug' => $slug]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update post']);
    }
    exit;
}

// DELETE - Delete existing post (authenticated only)
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
    $slug = $input['slug'] ?? '';

    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug required']);
        exit;
    }

    // Validate slug format to prevent path traversal
    if (!preg_match('/^[a-zA-Z0-9\-]+$/', $slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid slug format']);
        exit;
    }

    // Find existing file by slug
    $filepath = BLOGS_DIR . '/' . $slug . '.md';
    if (!file_exists($filepath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    if (unlink($filepath)) {
        echo json_encode(['success' => true, 'slug' => $slug]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete post']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

// Helper function to parse frontmatter
function parseFromtmatter($content) {
    $result = ['content' => $content];

    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
        $frontmatter = $matches[1];
        $result['content'] = trim($matches[2]);

        foreach (explode("\n", $frontmatter) as $line) {
            if (preg_match('/^(\w+):\s*"(.*)"\s*$/', $line, $m)) {
                // Handle quoted values
                $result[$m[1]] = stripslashes($m[2]);
            } elseif (preg_match('/^(\w+):\s*(.*)$/', $line, $m)) {
                $result[$m[1]] = $m[2];
            }
        }
    }

    return $result;
}
