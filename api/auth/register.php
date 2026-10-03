<?php
// api/auth/register.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/auth.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond_error(400, 'Geçersiz JSON');
}

// Verify CSRF token
if (!verify_csrf_token($input['csrf_token'] ?? '')) {
    respond_error(403, 'Güvenlik doğrulaması başarısız');
}

$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
    respond_error(422, 'Eksik alanlar', ['required' => ['first_name', 'last_name', 'email', 'password']]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond_error(422, 'Geçersiz e-posta');
}

// Password strength check
if (strlen($password) < 8) {
    respond_error(422, 'Şifre en az 8 karakter olmalı');
}

try {
    $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $checkStmt->bindValue(':email', $email, PDO::PARAM_STR);
    $checkStmt->execute();
    if ($checkStmt->fetch()) {
        respond_error(409, 'E-posta zaten kayıtlı');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role)
        VALUES (:first_name, :last_name, :email, :password, 'user')");
    $stmt->bindValue(':first_name', $firstName, PDO::PARAM_STR);
    $stmt->bindValue(':last_name', $lastName, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $hash, PDO::PARAM_STR);
    $stmt->execute();

    start_session();
    $_SESSION['user_id'] = (int) $pdo->lastInsertId();
    $_SESSION['user_role'] = 'user';

    respond_success([
        'id' => $_SESSION['user_id'],
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'role' => 'user'
    ], 'Kayıt başarılı');
} catch (Exception $e) {
    respond_error(500, 'Kayıt sırasında hata');
}
?>