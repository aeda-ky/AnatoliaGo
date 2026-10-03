<?php
// api/locations/index.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/transform.php';

$cityId = get_int_query('city_id');
$category = get_string_query('category');
$q = get_string_query('q');
$sort = get_string_query('sort', 'name');
$hasCoords = get_int_query('has_coords');
$hasImage = get_int_query('has_image');

list($page, $limit, $offset) = get_pagination();

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

if ($q !== '') {
    $where[] = '(l.name LIKE :q_name OR c.name LIKE :q_city OR l.category LIKE :q_category OR l.description LIKE :q_description)';
    $params[':q_name'] = '%' . $q . '%';
    $params[':q_city'] = '%' . $q . '%';
    $params[':q_category'] = '%' . $q . '%';
    $params[':q_description'] = '%' . $q . '%';
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

$orderSql = 'l.name ASC';
switch ($sort) {
    case 'rating':
        $orderSql = 'l.avg_rating IS NULL, l.avg_rating DESC, l.name ASC';
        break;
    case 'new':
        $orderSql = 'l.created_at DESC, l.name ASC';
        break;
    case 'old':
        $orderSql = 'l.created_at ASC, l.name ASC';
        break;
    case 'name':
    default:
        $orderSql = 'l.name ASC';
        break;
}

try {
    $countSql = "SELECT COUNT(*) AS total
        FROM locations l
        LEFT JOIN cities c ON l.city_id = c.id
        $whereSql";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
            l.lat, l.lng,
            l.city_id, c.name AS city_name
        FROM locations l
        LEFT JOIN cities c ON l.city_id = c.id
        $whereSql
        ORDER BY $orderSql
        LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll();
    $items = normalize_location_list($items);

    $meta = [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0
    ];

    respond_success($items, 'Locations fetched', $meta);
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>