<?php

// Basic sanitization and utility functions
function htmlSafeOutput($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function sanitize_input($data)
{
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

function validate_date($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function format_date($date, $format = 'Y-m-d')
{
    $d = new DateTime($date);
    return $d->format($format);
}

function get_current_date($format = 'Y-m-d')
{
    return (new DateTime())->format($format);
}

function debug($data)
{
    echo '<pre>' . print_r($data, true) . '</pre>';
}

function log_error($message)
{
    error_log($message);
}

function generate_random_string($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

function redirectTo($url)
{
    header("Location: $url");
    exit();
}

// from auth.php
function csrf_field()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['csrf_token'];
    return '<input type="hidden" name="csrf_token" value="' . htmlSafeOutput($token) . '">';
}

function verify_csrf()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die('CSRF token validation failed');
        }
    }
}

function is_logged_in()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['user']);
}

function logout()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

// array modifiers
function array_flatten($array)
{
    $result = [];
    array_walk_recursive($array, function ($a) use (&$result) {
        $result[] = $a;
    });
    return $result;
}

function array_unique_multidimensional($array)
{
    $serialized = array_map('serialize', $array);
    $unique = array_unique($serialized);
    return array_intersect_key($array, $unique);
}
function paginate($total_items, $current_page = 1, $items_per_page = 10)
{
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset
    ];
}

// UI helpers
function active_class($page)
{
    $current_file = basename($_SERVER['PHP_SELF']);
    return $current_file === $page ? 'active' : '';
}
