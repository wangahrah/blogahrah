<?php
// Blog configuration
define('PASSWORD_HASH', '$2b$12$Xg/R3kQqZS7c/tkBruEuBO7k959Pl.eYR8AGEGkjmdkEZx2NAQaHi');
define('BLOGS_DIR', __DIR__ . '/../blogs');
define('MEDIA_DIR', __DIR__ . '/../media');

// Allowed media types
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm']);
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
