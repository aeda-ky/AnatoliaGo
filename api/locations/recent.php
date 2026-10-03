<?php
// api/locations/recent.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/transform.php';

$limit = get_int_query('limit', 6);
$cityId = get_int_query('city_id');
$category = get_string_query('category');
$hasCoords = get_int_query('has_coords');
$hasImage = get_int_query('has_image');

if ($limit < 1) {
    $limit = 6;
}

$where = [];
$params = [];

if ($cityId) {
    $where[] = 'l.city_id = :city_id';
    $params[':city_id'] = $cityId;
}

if ($category !== '') {
    $where[] = 'l.category = :category';
    $params[':category'] = $category;
}

if ($hasCoords === 1) {
    $where[] = 'l.lat IS NOT NULL AND l.lng IS NOT NULL';
}

if ($hasImage === 1) {
    $where[] = 'l.image IS NOT NULL AND l.image <> ""';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

try {
    $sql = "SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
            l.lat, l.lng,
            l.city_id, c.name AS city_name
        FROM locations l
        LEFT JOIN cities c ON l.city_id = c.id
        $whereSql
        ORDER BY l.created_at DESC, l.name ASC
        LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll();
    $items = normalize_location_list($items);

    respond_success($items, 'Recent locations fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>