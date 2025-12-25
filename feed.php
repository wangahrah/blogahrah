<?php
require_once __DIR__ . '/api/config.php';

header('Content-Type: application/rss+xml; charset=UTF-8');

// Build base URL from request
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;

// Get posts (public only)
$posts = [];

if (is_dir(BLOGS_DIR)) {
    $files = glob(BLOGS_DIR . '/*.md');

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $meta = parseFrontmatter($content);
        $filename = basename($file);

        // Skip private posts
        $isPrivate = isset($meta['private']) && ($meta['private'] === 'true' || $meta['private'] === true);
        if ($isPrivate) {
            continue;
        }

        $posts[] = [
            'slug' => pathinfo($filename, PATHINFO_FILENAME),
            'title' => $meta['title'] ?? 'Untitled',
            'date' => $meta['date'] ?? substr($filename, 0, 10),
            'content' => $meta['content']
        ];
    }
}

// Sort by date descending and take only 5 most recent
usort($posts, fn($a, $b) => strcmp($b['date'], $a['date']));
$posts = array_slice($posts, 0, 5);

// Generate RSS
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>blogahrah</title>
    <link><?= htmlspecialchars($baseUrl) ?>/blogahrah/</link>
    <description>Michael's blog</description>
    <language>en-us</language>
    <atom:link href="<?= htmlspecialchars($baseUrl) ?>/feed.php" rel="self" type="application/rss+xml"/>
<?php foreach ($posts as $post):
    // Convert date to RFC 822 format
    $pubDate = date('r', strtotime($post['date']));
    $link = $baseUrl . '/blogahrah/?p=' . urlencode($post['slug']);

    // Create excerpt (strip markdown, limit to 300 chars)
    $excerpt = preg_replace('/!\[.*?\]\([^)]*\)/', '', $post['content']); // Remove images
    $excerpt = preg_replace('/<[^>]*>/', '', $excerpt); // Remove HTML
    $excerpt = preg_replace('/[#*_`\[\]()]/', '', $excerpt); // Remove markdown chars
    $excerpt = trim(substr($excerpt, 0, 300));
    if (strlen($post['content']) > 300) $excerpt .= '...';
?>
    <item>
      <title><?= htmlspecialchars($post['title']) ?></title>
      <link><?= htmlspecialchars($link) ?></link>
      <guid isPermaLink="true"><?= htmlspecialchars($link) ?></guid>
      <pubDate><?= $pubDate ?></pubDate>
      <description><?= htmlspecialchars($excerpt) ?></description>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
<?php

// Helper function to parse frontmatter
function parseFrontmatter($content) {
    $result = ['content' => $content];

    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
        $frontmatter = $matches[1];
        $result['content'] = trim($matches[2]);

        foreach (explode("\n", $frontmatter) as $line) {
            if (preg_match('/^(\w+):\s*"(.*)"\s*$/', $line, $m)) {
                $result[$m[1]] = stripslashes($m[2]);
            } elseif (preg_match('/^(\w+):\s*(.*)$/', $line, $m)) {
                $result[$m[1]] = $m[2];
            }
        }
    }

    return $result;
}
