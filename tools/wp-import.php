<?php
/**
 * WordPress XML to Markdown Converter
 *
 * Converts WordPress export XML to individual Markdown files
 * compatible with the blogahrah blog system.
 *
 * Usage:
 *   php tools/wp-import.php                    # Execute import
 *   php tools/wp-import.php --dry-run          # Preview without writing
 *   php tools/wp-import.php path/to/export.xml # Custom XML file
 */

// Configuration
$defaultXmlFile = __DIR__ . '/../blogahrah.WordPress.2025-12-25.xml';
$outputDir = __DIR__ . '/../blogs';

// Parse command line arguments
$dryRun = in_array('--dry-run', $argv);
$xmlFile = $defaultXmlFile;

foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if ($arg === '--dry-run') continue;
    if (file_exists($arg)) {
        $xmlFile = $arg;
    }
}

// Stats
$stats = [
    'processed' => 0,
    'skipped' => 0,
    'written' => 0,
    'errors' => 0,
];

echo "WordPress to Markdown Converter\n";
echo "================================\n";
echo "XML file: $xmlFile\n";
echo "Output dir: $outputDir\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no files will be written)" : "EXECUTE") . "\n\n";

if (!file_exists($xmlFile)) {
    die("Error: XML file not found: $xmlFile\n");
}

if (!is_dir($outputDir)) {
    die("Error: Output directory not found: $outputDir\n");
}

// Load XML content
$xmlContent = file_get_contents($xmlFile);
if ($xmlContent === false) {
    die("Error: Failed to read XML file\n");
}

// Extract all <item> blocks using regex
preg_match_all('/<item>(.*?)<\/item>/s', $xmlContent, $items);

// Track used filenames to avoid duplicates
$usedFilenames = [];

echo "Found " . count($items[1]) . " items in XML file\n\n";

// Process each item
foreach ($items[1] as $itemXml) {
    // Extract post type
    $postType = extractCdata($itemXml, 'wp:post_type');

    // Skip non-posts
    if ($postType !== 'post') {
        continue;
    }

    // Extract status
    $status = extractCdata($itemXml, 'wp:status');

    // Skip non-published posts
    if ($status !== 'publish') {
        $stats['skipped']++;
        continue;
    }

    $stats['processed']++;

    // Extract data
    $title = extractCdata($itemXml, 'title');
    $rawContent = extractCdata($itemXml, 'content:encoded');
    $postDate = extractCdata($itemXml, 'wp:post_date'); // Format: YYYY-MM-DD HH:MM:SS
    $postName = extractCdata($itemXml, 'wp:post_name');
    $postId = extractCdata($itemXml, 'wp:post_id');

    // Extract date (YYYY-MM-DD)
    $date = substr($postDate, 0, 10);

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo "Warning: Invalid date format for post $postId: $postDate\n";
        $date = date('Y-m-d'); // Fallback to today
    }

    // Convert HTML to Markdown
    $markdownContent = htmlToMarkdown($rawContent);

    // Extract title if empty
    if (empty($title)) {
        $title = extractTitle($markdownContent, $postId, $date);
    }

    // Generate slug
    $slug = generateSlug($postName, $title, $postId);

    // Build filename
    $filename = "$date-$slug.md";

    // Handle duplicates
    $baseFilename = $filename;
    $counter = 2;
    while (isset($usedFilenames[$filename])) {
        $filename = str_replace('.md', "-$counter.md", $baseFilename);
        $counter++;
    }
    $usedFilenames[$filename] = true;

    $filepath = "$outputDir/$filename";

    // Escape title for YAML (handle quotes)
    $yamlTitle = str_replace('"', '\\"', $title);

    // Build markdown file content
    $output = "---\n";
    $output .= "title: \"$yamlTitle\"\n";
    $output .= "date: $date\n";
    $output .= "private: false\n";
    $output .= "---\n\n";
    $output .= $markdownContent;

    // Output progress
    $shortTitle = strlen($title) > 50 ? substr($title, 0, 47) . '...' : $title;
    echo "[{$stats['processed']}] $filename\n";
    echo "    Title: $shortTitle\n";

    if ($dryRun) {
        echo "    [DRY RUN - not written]\n";
    } else {
        $result = file_put_contents($filepath, $output);
        if ($result === false) {
            echo "    [ERROR writing file]\n";
            $stats['errors']++;
        } else {
            echo "    [Written: " . strlen($output) . " bytes]\n";
            $stats['written']++;
        }
    }
    echo "\n";
}

// Summary
echo "================================\n";
echo "Summary:\n";
echo "  Processed: {$stats['processed']}\n";
echo "  Skipped (drafts): {$stats['skipped']}\n";
echo "  Written: {$stats['written']}\n";
echo "  Errors: {$stats['errors']}\n";

if ($dryRun) {
    echo "\nThis was a DRY RUN. No files were written.\n";
    echo "Run without --dry-run to execute the import.\n";
}

/**
 * Extract CDATA content from an XML tag
 */
function extractCdata(string $xml, string $tag): string {
    // Handle namespaced tags (e.g., wp:post_type, content:encoded)
    $escapedTag = preg_quote($tag, '/');

    // Match both CDATA wrapped and plain content
    if (preg_match("/<{$escapedTag}>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/{$escapedTag}>/s", $xml, $matches)) {
        return trim($matches[1]);
    }

    return '';
}

/**
 * Convert HTML content to Markdown
 */
function htmlToMarkdown(string $html): string {
    $md = $html;

    // Normalize line endings
    $md = str_replace(["\r\n", "\r"], "\n", $md);

    // Handle paragraphs first (before <br> to avoid double newlines)
    $md = preg_replace('/<p[^>]*>/i', '', $md);
    $md = preg_replace('/<\/p>/i', "\n\n", $md);

    // Convert line breaks
    $md = preg_replace('/<br\s*\/?>/i', "\n", $md);

    // Convert linked images first: <a href="LINK"><img src="IMG" /></a> -> [![alt](IMG)](LINK)
    // This preserves both the image AND the link
    $md = preg_replace_callback(
        '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>\s*<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>\s*<\/a>/is',
        function($matches) {
            $linkUrl = $matches[1];
            $imgSrc = $matches[2];
            // Try to extract alt text from the img tag
            if (preg_match('/alt=["\']([^"\']*)["\']/', $matches[0], $altMatch)) {
                $alt = $altMatch[1];
            } else {
                $alt = '';
            }
            // If link points to wp-content/uploads (just a lightbox to full image), return plain image
            // Otherwise return linked image: [![alt](img)](link)
            if (strpos($linkUrl, 'wp-content/uploads') !== false) {
                return "![$alt]($imgSrc)";
            }
            return "[![$alt]($imgSrc)]($linkUrl)";
        },
        $md
    );

    // Convert links: <a href="URL">text</a> -> [text](URL)
    $md = preg_replace_callback(
        '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is',
        function($matches) {
            $url = $matches[1];
            $text = strip_tags($matches[2]);
            return "[$text]($url)";
        },
        $md
    );

    // Convert images: <img src="URL" ...> -> ![alt](URL)
    $md = preg_replace_callback(
        '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i',
        function($matches) {
            $url = $matches[1];
            // Try to extract alt text
            if (preg_match('/alt=["\']([^"\']*)["\']/', $matches[0], $altMatch)) {
                $alt = $altMatch[1];
            } else {
                $alt = '';
            }
            return "![$alt]($url)";
        },
        $md
    );

    // Rewrite WordPress uploads to local /media/ path
    $md = preg_replace_callback(
        '/!\[([^\]]*)\]\(https?:\/\/[^)]*wangahrah\.com\/blogahrah\/wp-content\/uploads\/\d{4}\/\d{2}\/([^)]+)\)/i',
        fn($m) => "![{$m[1]}](/media/{$m[2]})",
        $md
    );

    // Replace dead external images with placeholder
    $md = preg_replace(
        '/!\[[^\]]*\]\(https?:\/\/(img\.photobucket\.com|homepage\.mac\.com)[^)]*\)/i',
        '[Image no longer available]',
        $md
    );

    // Convert bold: <strong>/<b> -> **...**
    $md = preg_replace('/<(strong|b)[^>]*>(.*?)<\/\1>/is', '**$2**', $md);

    // Convert italic: <em>/<i> -> *...*
    $md = preg_replace('/<(em|i)[^>]*>(.*?)<\/\1>/is', '*$2*', $md);

    // Convert headings
    $md = preg_replace('/<h1[^>]*>(.*?)<\/h1>/is', "# $1\n\n", $md);
    $md = preg_replace('/<h2[^>]*>(.*?)<\/h2>/is', "## $1\n\n", $md);
    $md = preg_replace('/<h3[^>]*>(.*?)<\/h3>/is', "### $1\n\n", $md);
    $md = preg_replace('/<h4[^>]*>(.*?)<\/h4>/is', "#### $1\n\n", $md);

    // Convert blockquotes
    $md = preg_replace_callback(
        '/<blockquote[^>]*>(.*?)<\/blockquote>/is',
        function($matches) {
            $content = trim(strip_tags($matches[1]));
            $lines = explode("\n", $content);
            return implode("\n", array_map(fn($l) => "> $l", $lines)) . "\n\n";
        },
        $md
    );

    // Convert unordered lists
    $md = preg_replace('/<ul[^>]*>/i', '', $md);
    $md = preg_replace('/<\/ul>/i', "\n", $md);
    $md = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "- $1\n", $md);

    // Convert ordered lists
    $md = preg_replace('/<ol[^>]*>/i', '', $md);
    $md = preg_replace('/<\/ol>/i', "\n", $md);

    // Handle nbsp
    $md = str_replace('&nbsp;', ' ', $md);

    // Strip remaining HTML tags
    $md = strip_tags($md);

    // Decode HTML entities
    $md = html_entity_decode($md, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Remove WordPress encoding artifacts (Â from broken UTF-8 non-breaking spaces)
    $md = str_replace(['Â ', 'Â'], [' ', ''], $md);

    // Fix smart quote/punctuation mojibake using hex bytes for accuracy
    $md = str_replace(
        [
            "\xC3\xA2\xE2\x82\xAC\xE2\x84\xA2", // â€™ -> '
            "\xC3\xA2\xE2\x82\xAC\xCB\x9C",     // â€˜ -> '
            "\xC3\xA2\xE2\x82\xAC\xC5\x93",     // â€œ -> "
            "\xC3\xA2\xE2\x82\xAC\xE2\x80\x9D", // â€ -> " (right double quote)
            "\xC3\xA2\xE2\x82\xAC\xE2\x80\x9C", // â€" -> — (em-dash)
            "\xC3\xA2\xE2\x82\xAC\xC2\xA6",     // â€¦ -> …
        ],
        ["'", "'", '"', '"', '—', '…'],
        $md
    );

    // Convert WordPress [caption] shortcodes to just the image with caption as italic text below
    $md = preg_replace_callback(
        '/\[caption[^\]]*caption=["\']([^"\']+)["\'][^\]]*\](!\[[^\]]*\]\([^)]+\))\[\/caption\]/i',
        fn($m) => $m[2] . "\n*" . $m[1] . "*",
        $md
    );
    // Also handle caption attribute in different position
    $md = preg_replace_callback(
        '/\[caption[^\]]*\](!\[[^\]]*\]\([^)]+\))\[\/caption\]/i',
        fn($m) => $m[1],
        $md
    );

    // Clean up excessive whitespace
    $md = preg_replace('/\n{3,}/', "\n\n", $md);
    $md = preg_replace('/[ \t]+\n/', "\n", $md); // Trailing whitespace
    $md = trim($md);

    return $md;
}

/**
 * Extract title from content when no title is provided
 */
function extractTitle(string $content, string $postId, string $date): string {
    // Look for patterns like: This post is entitled, "Title"
    if (preg_match('/(?:entitled|titled)[,:]?\s*["\']([^"\']+)["\']/i', $content, $matches)) {
        $title = trim($matches[1]);
        if (strlen($title) > 5 && strlen($title) < 100) {
            return $title;
        }
    }

    // Try first line if it's short enough (likely a title)
    $firstLine = strtok($content, "\n");
    $firstLine = trim($firstLine);

    // Remove markdown formatting
    $firstLine = preg_replace('/^#+\s*/', '', $firstLine);
    $firstLine = preg_replace('/\*\*([^*]+)\*\*/', '$1', $firstLine);

    if (strlen($firstLine) > 5 && strlen($firstLine) < 80 && !preg_match('/[.!?]$/', $firstLine)) {
        return $firstLine;
    }

    // Fallback to post ID
    return "Post $postId";
}

/**
 * Generate URL-friendly slug
 */
function generateSlug(string $postName, string $title, string $postId): string {
    // If postName looks like a real slug (not just a number), use it
    if (!empty($postName) && !is_numeric($postName) && strlen($postName) > 2) {
        return sanitizeSlug($postName);
    }

    // Generate from title
    $slug = $title;

    // Convert to lowercase
    $slug = strtolower($slug);

    // Replace spaces and underscores with hyphens
    $slug = preg_replace('/[\s_]+/', '-', $slug);

    // Remove non-alphanumeric characters (except hyphens)
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);

    // Trim hyphens from start and end
    $slug = trim($slug, '-');

    // Limit length
    if (strlen($slug) > 50) {
        $slug = substr($slug, 0, 50);
        $slug = rtrim($slug, '-');
    }

    // Fallback if empty
    if (empty($slug)) {
        $slug = "post-$postId";
    }

    return $slug;
}

/**
 * Sanitize an existing slug
 */
function sanitizeSlug(string $slug): string {
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
