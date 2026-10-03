<?php
// api/admin/users.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/auth.php';

if (!require_admin()) {
    respond_error(401, 'Yetkisiz');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = get_int_query('id');
    try {
        if ($id) {
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, total_routes_shared, created_at
                FROM users WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch();
            if (!$item) {
                respond_error(404, 'Kayıt bulunamadı');
            }
            respond_success($item, 'User fetched');
        }

        $stmt = $pdo->query("SELECT id, first_name, last_name, email, role, total_routes_shared, created_at
            FROM users ORDER BY id DESC");
        respond_success($stmt->fetchAll(), 'Users fetched');
    } catch (Exception $e) {
        respond_error(500, 'Sorgu hatası');
    }
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond_error(400, 'Geçersiz JSON');
}

if ($method === 'PUT') {
    $csrf = $input['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf)) {
        respond_error(403, 'CSRF doğrulama başarısız');
    }

    $id = (int) ($input['id'] ?? 0);
    $role = trim($input['role'] ?? '');

    if ($id < 1 || $role === '') {
        respond_error(422, 'Eksik alanlar', ['required' => ['id', 'role']]);
    }

    if (!in_array($role, ['admin', 'user'], true)) {
        respond_error(422, 'Geçersiz rol');
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        respond_success(['id' => $id, 'role' => $role], 'User role updated');
    } catch (Exception $e) {
        respond_error(500, 'Güncelleme hatası');
    }
}

if ($method === 'DELETE') {
    $csrf = $input['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf)) {
        respond_error(403, 'CSRF doğrulama başarısız');
    }

    $id = (int) ($input['id'] ?? 0);
    if ($id < 1) {
        respond_error(422, 'Eksik id');
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        respond_success(['id' => $id], 'User deleted');
    } catch (Exception $e) {
        respond_error(500, 'Silme hatası');
    }
}

respond_error(405, 'Method not allowed');
?>