<?php
/**
 * Download Medium CDN images locally and update blog posts
 * Run: php tools/download-medium-images.php
 */

$blogsDir = __DIR__ . '/../blogs';
$mediaDir = __DIR__ . '/../media';
$maxSize = 5 * 1024 * 1024; // 5MB

if (!is_dir($mediaDir)) {
    mkdir($mediaDir, 0755, true);
}

$files = glob($blogsDir . '/*.md');
$totalDownloaded = 0;
$totalUpdated = 0;
$errors = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $filename = basename($file);
    $updated = false;

    // Find all Medium CDN URLs
    // Markdown format: ![alt](url)
    // HTML format: <img src="url"
    $patterns = [
        '/!\[([^\]]*)\]\((https:\/\/cdn-images-1\.medium\.com\/[^)]+)\)/',
        '/<img([^>]*)src=["\']?(https:\/\/cdn-images-1\.medium\.com\/[^"\'>\s]+)["\']?([^>]*)>/'
    ];

    foreach ($patterns as $patternIndex => $pattern) {
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $fullMatch = $match[0];
                $url = $patternIndex === 0 ? $match[2] : $match[2];
                $alt = $patternIndex === 0 ? $match[1] : '';

                echo "Downloading: $url\n";

                // Try alternate URL format if original fails
                $urlsToTry = [$url];
                // Medium changed from /max/SIZE/ to /v2/resize:fit:SIZE/
                if (strpos($url, '/max/') !== false) {
                    $altUrl = preg_replace('/\/max\/(\d+)\//', '/v2/resize:fit:$1/', $url);
                    $urlsToTry[] = $altUrl;
                }

                // Generate local filename
                $urlHash = substr(md5($url), 0, 8);
                $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $datePrefix = substr($filename, 0, 10); // Get date from blog filename
                $localFilename = $datePrefix . '-medium-' . $urlHash . '.' . $ext;
                $localPath = $mediaDir . '/' . $localFilename;
                $localUrl = '/media/' . $localFilename;

                // Skip if already downloaded
                if (file_exists($localPath)) {
                    echo "  Already exists: $localFilename\n";
                } else {
                    // Try each URL until one works
                    $imageData = false;
                    $workingUrl = null;
                    foreach ($urlsToTry as $tryUrl) {
                        $imageData = @file_get_contents($tryUrl);
                        if ($imageData !== false) {
                            $workingUrl = $tryUrl;
                            if ($tryUrl !== $url) {
                                echo "  Used alternate URL: $tryUrl\n";
                            }
                            break;
                        }
                    }

                    if ($imageData === false) {
                        $errors[] = "Failed to download: $url in $filename";
                        echo "  ERROR: Failed to download\n";
                        continue;
                    }

                    file_put_contents($localPath, $imageData);
                    $totalDownloaded++;
                    echo "  Saved: $localFilename (" . round(filesize($localPath) / 1024) . "KB)\n";

                    // Rate limit delay to avoid 429 errors
                    usleep(500000); // 0.5 second delay

                    // Resize if too large
                    if (filesize($localPath) > $maxSize) {
                        echo "  Resizing (over 5MB)...\n";
                        $resized = resizeImage($localPath, $maxSize);
                        if ($resized) {
                            echo "  Resized to: " . round(filesize($localPath) / 1024) . "KB\n";
                        } else {
                            echo "  WARNING: Could not resize\n";
                        }
                    }
                }

                // Update content with local URL
                if ($patternIndex === 0) {
                    // Markdown
                    $replacement = '![' . $alt . '](' . $localUrl . ')';
                } else {
                    // HTML img tag
                    $replacement = '<img' . $match[1] . 'src="' . $localUrl . '"' . $match[3] . '>';
                }

                $content = str_replace($fullMatch, $replacement, $content);
                $updated = true;
            }
        }
    }

    if ($updated) {
        file_put_contents($file, $content);
        $totalUpdated++;
        echo "Updated: $filename\n\n";
    }
}

echo "\n=== Summary ===\n";
echo "Images downloaded: $totalDownloaded\n";
echo "Posts updated: $totalUpdated\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

/**
 * Resize image to fit under max size
 */
function resizeImage($filepath, $maxSize) {
    $info = getimagesize($filepath);
    if (!$info) return false;

    $mimeType = $info['mime'];

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

    for ($scale = 0.9; $scale >= 0.2; $scale -= 0.1) {
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($mimeType === 'image/png' || $mimeType === 'image/gif' || $mimeType === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
        }

        imagecopyresampled($resized, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

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

        if (($mimeType === 'image/jpeg' || $mimeType === 'image/webp') && $quality > 50) {
            $quality -= 10;
            $scale += 0.1;
        }
    }

    imagedestroy($img);
    return false;
}
