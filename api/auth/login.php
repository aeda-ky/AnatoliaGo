<?php
// api/auth/login.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/auth.php';

// Validate JSON and CSRF token
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond_error(400, 'Geçersiz JSON');
}

// Verify CSRF token
if (!verify_csrf_token($input['csrf_token'] ?? '')) {
    respond_error(403, 'Güvenlik doğrulaması başarısız');
}

$email = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

if ($email === '' || $password === '') {
    respond_error(422, 'Eksik alanlar', ['required' => ['email', 'password']]);
}

try {
    $stmt = $pdo->prepare('SELECT id, first_name, last_name, email, password, role FROM users WHERE email = :email LIMIT 1');
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        respond_error(401, 'E-posta veya şifre hatalı');
    }

    start_session();
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';

    respond_success([
        'id' => (int) $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user'
    ], 'Giriş başarılı');
} catch (Exception $e) {
    respond_error(500, 'Giriş sırasında hata');
}
?>