<?php
// includes/helpers/auth.php

function start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        // Security: Set secure session configuration
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        session_set_cookie_params([
            'lifetime' => 3600, // 1 hour
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'secure' => !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']), // Only HTTPS in production
            'httponly' => true, // Prevent JavaScript access
            'samesite' => 'Strict' // Prevent CSRF
        ]);
        session_start();

        // Regenerate session ID on each request
        if (!isset($_SESSION['last_regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['last_regenerated'] = time();
        } elseif (time() - $_SESSION['last_regenerated'] > 1800) { // Every 30 min
            session_regenerate_id(true);
            $_SESSION['last_regenerated'] = time();
        }
    }
}

function generate_csrf_token()
{
    start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_csrf_token()
{
    start_session();
    return $_SESSION['csrf_token'] ?? null;
}

function verify_csrf_token($token)
{
    start_session();
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function current_user_id()
{
    start_session();
    return $_SESSION['user_id'] ?? null;
}

function current_user_role()
{
    start_session();
    return $_SESSION['user_role'] ?? 'user';
}

function require_auth()
{
    start_session();
    return isset($_SESSION['user_id']);
}

function require_admin()
{
    start_session();
    return isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? 'user') === 'admin';
}
?>