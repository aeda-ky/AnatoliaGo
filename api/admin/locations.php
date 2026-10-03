<?php
// api/admin/locations.php

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
            $stmt = $pdo->prepare("SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
                    l.lat, l.lng, l.city_id, c.name AS city_name
                FROM locations l
                LEFT JOIN cities c ON l.city_id = c.id
                WHERE l.id = :id
                LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch();
            if (!$item) {
                respond_error(404, 'Kayıt bulunamadı');
            }
            respond_success($item, 'Location fetched');
        }

        $stmt = $pdo->query("SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
                l.lat, l.lng, l.city_id, c.name AS city_name
            FROM locations l
            LEFT JOIN cities c ON l.city_id = c.id
            ORDER BY l.id DESC");
        respond_success($stmt->fetchAll(), 'Locations fetched');
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
    $category = trim($input['category'] ?? '');
    $cityId = (int) ($input['city_id'] ?? 0);
    $description = trim($input['description'] ?? '');
    $image = trim($input['image'] ?? '');
    $avgRating = $input['avg_rating'] ?? null;
    $lat = $input['lat'] ?? null;
    $lng = $input['lng'] ?? null;

    if ($name === '' || $category === '' || $cityId < 1) {
        respond_error(422, 'Eksik alanlar', ['required' => ['name', 'category', 'city_id']]);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO locations
            (name, category, city_id, lat, lng, description, image, avg_rating)
            VALUES (:name, :category, :city_id, :lat, :lng, :description, :image, :avg_rating)");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':city_id', $cityId, PDO::PARAM_INT);
        $stmt->bindValue(':lat', $lat);
        $stmt->bindValue(':lng', $lng);
        $stmt->bindValue(':description', $description !== '' ? $description : null);
        $stmt->bindValue(':image', $image !== '' ? $image : null);
        $stmt->bindValue(':avg_rating', $avgRating);
        $stmt->execute();

        respond_success(['id' => (int) $pdo->lastInsertId()], 'Location created');
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
        'category' => trim($input['category'] ?? ''),
        'city_id' => $input['city_id'] ?? null,
        'lat' => $input['lat'] ?? null,
        'lng' => $input['lng'] ?? null,
        'description' => $input['description'] ?? null,
        'image' => $input['image'] ?? null,
        'avg_rating' => $input['avg_rating'] ?? null
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
        $sql = "UPDATE locations SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        respond_success(['id' => $id], 'Location updated');
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
        $stmt = $pdo->prepare('DELETE FROM locations WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        respond_success(['id' => $id], 'Location deleted');
    } catch (Exception $e) {
        respond_error(500, 'Silme hatası');
    }
}

respond_error(405, 'Method not allowed');
?>