<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// GET - List all posts (public)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $posts = [];

    if (!is_dir(BLOGS_DIR)) {
        echo json_encode([]);
        exit;
    }

    $files = glob(BLOGS_DIR . '/*.md');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $meta = parseFromtmatter($content);
        $filename = basename($file);

        $posts[] = [
            'filename' => $filename,
            'slug' => pathinfo($filename, PATHINFO_FILENAME),
            'title' => $meta['title'] ?? 'Untitled',
            'date' => $meta['date'] ?? substr($filename, 0, 10),
            'content' => $meta['content']
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

    $input = json_decode(file_get_contents('php://input'), true);
    $title = $input['title'] ?? '';
    $content = $input['content'] ?? '';
    $date = $input['date'] ?? date('Y-m-d');

    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and content required']);
        exit;
    }

    // Create slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    $filename = $date . '-' . $slug . '.md';
    $filepath = BLOGS_DIR . '/' . $filename;

    // Don't overwrite existing posts
    if (file_exists($filepath)) {
        http_response_code(409);
        echo json_encode(['error' => 'Post with this title and date already exists']);
        exit;
    }

    // Create markdown with frontmatter
    $markdown = "---\n";
    $markdown .= "title: " . $title . "\n";
    $markdown .= "date: " . $date . "\n";
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

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

// Helper function to parse frontmatter
function parseFromtmatter($content) {
    $result = ['content' => $content];

    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
        $frontmatter = $matches[1];
        $result['content'] = trim($matches[2]);

        foreach (explode("\n", $frontmatter) as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', $line, $m)) {
                $result[$m[1]] = $m[2];
            }
        }
    }

    return $result;
}
