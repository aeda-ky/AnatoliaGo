<?php
// api/admin/ratings.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/auth.php';

if (!require_admin()) {
    respond_error(401, 'Yetkisiz');
}

function recalc_location_avg($pdo, $locationId)
{
    $avgStmt = $pdo->prepare('SELECT AVG(star_count) AS avg_rating FROM ratings WHERE location_id = :location_id');
    $avgStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
    $avgStmt->execute();
    $avg = $avgStmt->fetchColumn();

    $updateStmt = $pdo->prepare('UPDATE locations SET avg_rating = :avg_rating WHERE id = :location_id');
    $updateStmt->bindValue(':avg_rating', $avg !== null ? round((float) $avg, 2) : null);
    $updateStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
    $updateStmt->execute();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = get_int_query('id');
    try {
        if ($id) {
            $stmt = $pdo->prepare("SELECT rating_id, location_id, user_id, star_count, rating_date
                FROM ratings WHERE rating_id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch();
            if (!$item) {
                respond_error(404, 'Kayıt bulunamadı');
            }
            respond_success($item, 'Rating fetched');
        }

        $stmt = $pdo->query("SELECT rating_id, location_id, user_id, star_count, rating_date
            FROM ratings ORDER BY rating_date DESC");
        respond_success($stmt->fetchAll(), 'Ratings fetched');
    } catch (Exception $e) {
        respond_error(500, 'Sorgu hatası');
    }
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond_error(400, 'Geçersiz JSON');
}

if ($method === 'POST') {
    $locationId = (int) ($input['location_id'] ?? 0);
    $userId = (int) ($input['user_id'] ?? 0);
    $starCount = (int) ($input['star_count'] ?? 0);

    if ($locationId < 1 || $userId < 1 || $starCount < 1) {
        respond_error(422, 'Eksik alanlar', ['required' => ['location_id', 'user_id', 'star_count']]);
    }

    if ($starCount < 1 || $starCount > 5) {
        respond_error(422, 'Puan 1-5 arası olmalı');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO ratings (location_id, user_id, star_count)
            VALUES (:location_id, :user_id, :star_count)");
        $stmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':star_count', $starCount, PDO::PARAM_INT);
        $stmt->execute();

        recalc_location_avg($pdo, $locationId);

        respond_success(['rating_id' => (int) $pdo->lastInsertId()], 'Rating created');
    } catch (Exception $e) {
        respond_error(500, 'Kayıt oluşturma hatası');
    }
}

if ($method === 'PUT') {
    $id = (int) ($input['rating_id'] ?? 0);
    if ($id < 1) {
        respond_error(422, 'Eksik rating_id');
    }

    $fields = [
        'location_id' => $input['location_id'] ?? null,
        'user_id' => $input['user_id'] ?? null,
        'star_count' => $input['star_count'] ?? null
    ];

    $setParts = [];
    $params = [':rating_id' => $id];

    foreach ($fields as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        if ($key === 'star_count' && ($value < 1 || $value > 5)) {
            respond_error(422, 'Puan 1-5 arası olmalı');
        }
        $setParts[] = "$key = :$key";
        $params[":$key"] = $value;
    }

    if (empty($setParts)) {
        respond_error(422, 'Güncellenecek alan yok');
    }

    try {
        $oldStmt = $pdo->prepare('SELECT location_id FROM ratings WHERE rating_id = :rating_id');
        $oldStmt->bindValue(':rating_id', $id, PDO::PARAM_INT);
        $oldStmt->execute();
        $oldLocationId = $oldStmt->fetchColumn();

        $sql = "UPDATE ratings SET " . implode(', ', $setParts) . " WHERE rating_id = :rating_id";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $newLocationId = $fields['location_id'] ?? $oldLocationId;
        if ($oldLocationId) {
            recalc_location_avg($pdo, (int) $oldLocationId);
        }
        if ($newLocationId && $newLocationId !== $oldLocationId) {
            recalc_location_avg($pdo, (int) $newLocationId);
        }
        respond_success(['rating_id' => $id], 'Rating updated');
    } catch (Exception $e) {
        respond_error(500, 'Güncelleme hatası');
    }
}

if ($method === 'DELETE') {
    $id = (int) ($input['rating_id'] ?? 0);
    if ($id < 1) {
        respond_error(422, 'Eksik rating_id');
    }

    try {
        $locStmt = $pdo->prepare('SELECT location_id FROM ratings WHERE rating_id = :rating_id');
        $locStmt->bindValue(':rating_id', $id, PDO::PARAM_INT);
        $locStmt->execute();
        $locationId = $locStmt->fetchColumn();

        $stmt = $pdo->prepare('DELETE FROM ratings WHERE rating_id = :rating_id');
        $stmt->bindValue(':rating_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($locationId) {
            recalc_location_avg($pdo, (int) $locationId);
        }
        respond_success(['rating_id' => $id], 'Rating deleted');
    } catch (Exception $e) {
        respond_error(500, 'Silme hatası');
    }
}

respond_error(405, 'Method not allowed');
?>