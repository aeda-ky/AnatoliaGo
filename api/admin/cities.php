<?php
// api/admin/cities.php

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
            $stmt = $pdo->prepare("SELECT id, name, plate_code, created_at FROM cities WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch();
            if (!$item) {
                respond_error(404, 'Kayıt bulunamadı');
            }
            respond_success($item, 'City fetched');
        }

        $stmt = $pdo->query("SELECT id, name, plate_code, created_at FROM cities ORDER BY id DESC");
        respond_success($stmt->fetchAll(), 'Cities fetched');
    } catch (Exception $e) {
        respond_error(500, 'Sorgu hatası');
    }
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond_error(400, 'Geçersiz JSON');
}

if ($method === 'POST') {
    $csrf = $input['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf)) {
        respond_error(403, 'CSRF doğrulama başarısız');
    }

    $name = trim($input['name'] ?? '');
    $plateCode = trim($input['plate_code'] ?? '');

    if ($name === '' || $plateCode === '') {
        respond_error(422, 'Eksik alanlar', ['required' => ['name', 'plate_code']]);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO cities (name, plate_code) VALUES (:name, :plate_code)");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':plate_code', $plateCode, PDO::PARAM_STR);
        $stmt->execute();

        respond_success(['id' => (int) $pdo->lastInsertId()], 'City created');
    } catch (Exception $e) {
        respond_error(500, 'Kayıt oluşturma hatası');
    }
}

if ($method === 'PUT') {
    $csrf = $input['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf)) {
        respond_error(403, 'CSRF doğrulama başarısız');
    }

    $id = (int) ($input['id'] ?? 0);
    if ($id < 1) {
        respond_error(422, 'Eksik id');
    }

    $fields = [
        'name' => trim($input['name'] ?? ''),
        'plate_code' => trim($input['plate_code'] ?? '')
    ];

    $setParts = [];
    $params = [':id' => $id];

    foreach ($fields as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $setParts[] = "$key = :$key";
        $params[":$key"] = $value;
    }

    if (empty($setParts)) {
        respond_error(422, 'Güncellenecek alan yok');
    }

    try {
        $sql = "UPDATE cities SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        respond_success(['id' => $id], 'City updated');
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
        $stmt = $pdo->prepare('DELETE FROM cities WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        respond_success(['id' => $id], 'City deleted');
    } catch (Exception $e) {
        respond_error(500, 'Silme hatası');
    }
}

respond_error(405, 'Method not allowed');
?>