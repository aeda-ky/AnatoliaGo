<?php
// api/routes/index.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$cityId = get_int_query('city_id');
$q = get_string_query('q');
$sort = get_string_query('sort', 'new');
$isPublic = get_int_query('is_public');

list($page, $limit, $offset) = get_pagination();

$where = [];
$params = [];

if ($cityId) {
    $where[] = 'r.city_id = :city_id';
    $params[':city_id'] = $cityId;
}

if ($q !== '') {
    $where[] = '(r.route_name LIKE :q OR c.name LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}

if ($isPublic !== null) {
    $where[] = 'r.is_public = :is_public';
    $params[':is_public'] = $isPublic ? 1 : 0;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$orderSql = $sort === 'old' ? 'r.creation_date ASC' : 'r.creation_date DESC';

try {
    $countSql = "SELECT COUNT(*) AS total
        FROM routes r
        LEFT JOIN cities c ON r.city_id = c.id
        $whereSql";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

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

    $meta = [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0
    ];

    respond_success($items, 'Routes fetched', $meta);
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>