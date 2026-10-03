<?php
// api/routes/recent.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$limit = get_int_query('limit', 6);
$cityId = get_int_query('city_id');
$isPublic = get_int_query('is_public', 1);

if ($limit < 1) {
    $limit = 6;
}

$where = [];
$params = [];

if ($cityId) {
    $where[] = 'r.city_id = :city_id';
    $params[':city_id'] = $cityId;
}

if ($isPublic !== null) {
    $where[] = 'r.is_public = :is_public';
    $params[':is_public'] = $isPublic ? 1 : 0;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

try {
    $sql = "SELECT r.route_id, r.route_name, r.is_public, r.creation_date,
            r.city_id, c.name AS city_name,
            r.user_id, CONCAT(u.first_name, ' ', u.last_name) AS author_name,
            COUNT(rd.detail_id) AS stops
        FROM routes r
        LEFT JOIN cities c ON r.city_id = c.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN route_details rd ON rd.route_id = r.route_id
        $whereSql
        GROUP BY r.route_id
        ORDER BY r.creation_date DESC
        LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll();

    respond_success($items, 'Recent routes fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>