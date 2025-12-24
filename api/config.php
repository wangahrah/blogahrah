<?php
// Blog configuration
define('PASSWORD_HASH', '$2b$12$Xg/R3kQqZS7c/tkBruEuBO7k959Pl.eYR8AGEGkjmdkEZx2NAQaHi');
define('BLOGS_DIR', __DIR__ . '/../blogs');
define('MEDIA_DIR', __DIR__ . '/../media');

// Allowed media types with their valid extensions
define('ALLOWED_TYPES', [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
    'image/gif' => ['gif'],
    'image/webp' => ['webp'],
    'video/mp4' => ['mp4'],
    'video/webm' => ['webm']
]);
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// CSRF protection functions
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCsrfTokenFromRequest() {
    // Check header first, then POST body
    return $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
}
