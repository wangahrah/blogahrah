<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Check if already logged in
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $isAuth = isset($_SESSION['authenticated']) && $_SESSION['authenticated'];
    $response = ['authenticated' => $isAuth];
    if ($isAuth) {
        $response['csrf_token'] = generateCsrfToken();
    }
    echo json_encode($response);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? '';

    if (password_verify($password, PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        echo json_encode(['success' => true, 'csrf_token' => generateCsrfToken()]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid password']);
    }
    exit;
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
