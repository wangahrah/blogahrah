<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Check if already logged in
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['authenticated' => isset($_SESSION['authenticated']) && $_SESSION['authenticated']]);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? '';

    if (password_verify($password, PASSWORD_HASH)) {
        $_SESSION['authenticated'] = true;
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid password']);
    }
    exit;
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
