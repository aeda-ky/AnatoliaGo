<?php
// api/ratings/index.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/transform.php';

$locationId = get_int_query('location_id');
$userId = get_int_query('user_id');
$minStar = get_int_query('min_star');
$maxStar = get_int_query('max_star');
$sort = get_string_query('sort', 'new');

list($page, $limit, $offset) = get_pagination();

$where = [];
$params = [];

if ($locationId) {
    $where[] = 'r.location_id = :location_id';
    $params[':location_id'] = $locationId;
}

if ($userId) {
    $where[] = 'r.user_id = :user_id';
    $params[':user_id'] = $userId;
}

if ($minStar !== null) {
    $where[] = 'r.star_count >= :min_star';
    $params[':min_star'] = $minStar;
}

if ($maxStar !== null) {
    $where[] = 'r.star_count <= :max_star';
    $params[':max_star'] = $maxStar;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$orderSql = $sort === 'old' ? 'r.rating_date ASC' : 'r.rating_date DESC';

try {
    $countSql = "SELECT COUNT(*) AS total
        FROM ratings r
        $whereSql";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT r.rating_id, r.location_id, r.user_id, r.star_count, r.rating_date,
            l.name AS location_name, l.image, l.avg_rating,
            l.city_id, c.name AS city_name,
            CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM ratings r
        LEFT JOIN locations l ON r.location_id = l.id
        LEFT JOIN cities c ON l.city_id = c.id
        LEFT JOIN users u ON r.user_id = u.id
        $whereSql
        ORDER BY $orderSql
        LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll();
    foreach ($items as $index => $item) {
        $items[$index]['image_url'] = normalize_image_url($item['image'] ?? null);
    }

    $meta = [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0
    ];

    respond_success($items, 'Ratings fetched', $meta);
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>