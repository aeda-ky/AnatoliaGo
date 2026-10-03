<?php
// api/admin/routes.php

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
            $stmt = $pdo->prepare("SELECT r.route_id, r.route_name, r.is_public, r.creation_date,
                    r.city_id, c.name AS city_name,
                    r.user_id, CONCAT(u.first_name, ' ', u.last_name) AS author_name
                FROM routes r
                LEFT JOIN cities c ON r.city_id = c.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.route_id = :id
                LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch();
            if (!$item) {
                respond_error(404, 'Kayıt bulunamadı');
            }

            $detailsStmt = $pdo->prepare("SELECT rd.location_id FROM route_details rd WHERE rd.route_id = :id ORDER BY rd.order_number");
            $detailsStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $detailsStmt->execute();
            $item['location_ids'] = array_column($detailsStmt->fetchAll(), 'location_id');

            respond_success($item, 'Route fetched');
        }

        $stmt = $pdo->query("SELECT r.route_id, r.route_name, r.is_public, r.creation_date,
                r.city_id, c.name AS city_name,
                r.user_id, CONCAT(u.first_name, ' ', u.last_name) AS author_name
            FROM routes r
            LEFT JOIN cities c ON r.city_id = c.id
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.route_id DESC");
        respond_success($stmt->fetchAll(), 'Routes fetched');
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

    $routeName = trim($input['route_name'] ?? '');
    $userId = (int) ($input['user_id'] ?? 0);
    $cityId = (int) ($input['city_id'] ?? 0);
    $isPublic = (int) ($input['is_public'] ?? 1);
    $locationIds = is_array($input['location_ids']) ? array_map('intval', $input['location_ids']) : [];

    if ($routeName === '' || $userId < 1 || $cityId < 1 || empty($locationIds)) {
        respond_error(422, 'Eksik alanlar', ['required' => ['route_name', 'user_id', 'city_id', 'location_ids']]);
    }

    if (count(array_filter($locationIds, fn($id) => $id < 1)) > 0) {
        respond_error(422, 'Geçersiz durak seçimi');
    }

    try {
        $validStmt = $pdo->prepare('SELECT COUNT(*) FROM locations WHERE id IN (' . implode(',', array_fill(0, count($locationIds), '?')) . ')');
        foreach ($locationIds as $index => $locId) {
            $validStmt->bindValue($index + 1, $locId, PDO::PARAM_INT);
        }
        $validStmt->execute();
        if ($validStmt->fetchColumn() !== count($locationIds)) {
            respond_error(422, 'Geçersiz durak seçimi');
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO routes (user_id, city_id, route_name, is_public)
            VALUES (:user_id, :city_id, :route_name, :is_public)");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':city_id', $cityId, PDO::PARAM_INT);
        $stmt->bindValue(':route_name', $routeName, PDO::PARAM_STR);
        $stmt->bindValue(':is_public', $isPublic ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();
        $routeId = (int) $pdo->lastInsertId();

        $detailStmt = $pdo->prepare('INSERT INTO route_details (route_id, location_id, order_number) VALUES (:route_id, :location_id, :order_number)');
        foreach ($locationIds as $order => $locationId) {
            $detailStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
            $detailStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
            $detailStmt->bindValue(':order_number', $order + 1, PDO::PARAM_INT);
            $detailStmt->execute();
        }

        $pdo->commit();
        respond_success(['route_id' => $routeId], 'Route created');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        respond_error(500, 'Kayıt oluşturma hatası');
    }
}

if ($method === 'PUT') {
    $csrf = $input['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf)) {
        respond_error(403, 'CSRF doğrulama başarısız');
    }

    $id = (int) ($input['route_id'] ?? 0);
    if ($id < 1) {
        respond_error(422, 'Eksik route_id');
    }

    $fields = [
        'route_name' => trim($input['route_name'] ?? ''),
        'user_id' => $input['user_id'] ?? null,
        'city_id' => $input['city_id'] ?? null,
        'is_public' => $input['is_public'] ?? null
    ];
    $locationIds = is_array($input['location_ids']) ? array_map('intval', $input['location_ids']) : null;

    $setParts = [];
    $params = [':route_id' => $id];

    foreach ($fields as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $setParts[] = "$key = :$key";
        $params[":$key"] = $value;
    }

    if ($locationIds !== null && count(array_filter($locationIds, fn($id) => $id < 1)) > 0) {
        respond_error(422, 'Geçersiz durak seçimi');
    }

    try {
        $pdo->beginTransaction();

        if (!empty($setParts)) {
            $sql = "UPDATE routes SET " . implode(', ', $setParts) . " WHERE route_id = :route_id";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
        }

        if ($locationIds !== null) {
            if (!empty($locationIds)) {
                $validStmt = $pdo->prepare('SELECT COUNT(*) FROM locations WHERE id IN (' . implode(',', array_fill(0, count($locationIds), '?')) . ')');
                foreach ($locationIds as $index => $locId) {
                    $validStmt->bindValue($index + 1, $locId, PDO::PARAM_INT);
                }
                $validStmt->execute();
                if ($validStmt->fetchColumn() !== count($locationIds)) {
                    $pdo->rollBack();
                    respond_error(422, 'Geçersiz durak seçimi');
                }
            }

            $deleteStmt = $pdo->prepare('DELETE FROM route_details WHERE route_id = :route_id');
            $deleteStmt->bindValue(':route_id', $id, PDO::PARAM_INT);
            $deleteStmt->execute();

            if (!empty($locationIds)) {
                $detailStmt = $pdo->prepare('INSERT INTO route_details (route_id, location_id, order_number) VALUES (:route_id, :location_id, :order_number)');
                foreach ($locationIds as $order => $locationId) {
                    $detailStmt->bindValue(':route_id', $id, PDO::PARAM_INT);
                    $detailStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
                    $detailStmt->bindValue(':order_number', $order + 1, PDO::PARAM_INT);
                    $detailStmt->execute();
                }
            }
        }

        $pdo->commit();
        respond_success(['route_id' => $id], 'Route updated');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        respond_error(500, 'Güncelleme hatası');
    }
}

if ($method === 'DELETE') {
    $csrf = $input['csrf_token'] ?? null;
    if (!verify_csrf_token($csrf)) {
        respond_error(403, 'CSRF doğrulama başarısız');
    }

    $id = (int) ($input['route_id'] ?? 0);
    if ($id < 1) {
        respond_error(422, 'Eksik route_id');
    }

    try {
        $pdo->beginTransaction();
        $deleteDetails = $pdo->prepare('DELETE FROM route_details WHERE route_id = :route_id');
        $deleteDetails->bindValue(':route_id', $id, PDO::PARAM_INT);
        $deleteDetails->execute();

        $stmt = $pdo->prepare('DELETE FROM routes WHERE route_id = :route_id');
        $stmt->bindValue(':route_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();
        respond_success(['route_id' => $id], 'Route deleted');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        respond_error(500, 'Silme hatası');
    }
}

respond_error(405, 'Method not allowed');
?>