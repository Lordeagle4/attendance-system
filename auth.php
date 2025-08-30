<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}


function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: index.php?msg=Please+login');
        exit;
    }
}


function current_user_name(): string
{
    return $_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'User';
}


// Simple CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}


function csrf_field(): string
{
    $token = htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf" value="' . $token . '">';
}


function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sent = $_POST['csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
            http_response_code(400);
            die('Invalid CSRF token');
        }
    }
}