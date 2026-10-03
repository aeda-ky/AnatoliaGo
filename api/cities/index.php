<?php
// api/cities/index.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$q = get_string_query('q');
list($page, $limit, $offset) = get_pagination();

$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE c.name LIKE :q';
    $params[':q'] = '%' . $q . '%';
}

try {
    $countSql = "SELECT COUNT(*) AS total
        FROM cities c
        $where";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT c.id, c.name, c.plate_code,
            COUNT(DISTINCT l.id) AS location_count,
            COUNT(DISTINCT r.route_id) AS route_count
        FROM cities c
        LEFT JOIN locations l ON l.city_id = c.id
        LEFT JOIN routes r ON r.city_id = c.id
        $where
        GROUP BY c.id
        ORDER BY c.name ASC
        LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
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

    respond_success($items, 'Cities fetched', $meta);
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>