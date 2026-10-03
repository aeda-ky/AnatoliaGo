<?php
// api/users/index.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$q = get_string_query('q');
$role = get_string_query('role');
$sort = get_string_query('sort', 'new');

list($page, $limit, $offset) = get_pagination();

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}

if ($role !== '') {
    $where[] = 'u.role = :role';
    $params[':role'] = $role;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$orderSql = $sort === 'old' ? 'u.created_at ASC' : 'u.created_at DESC';

try {
    $countSql = "SELECT COUNT(*) AS total
        FROM users u
        $whereSql";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.total_routes_shared, u.created_at,
            COUNT(DISTINCT r.route_id) AS route_count,
            COUNT(DISTINCT rt.rating_id) AS rating_count
        FROM users u
        LEFT JOIN routes r ON r.user_id = u.id
        LEFT JOIN ratings rt ON rt.user_id = u.id
        $whereSql
        GROUP BY u.id
        ORDER BY $orderSql
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

    respond_success($items, 'Users fetched', $meta);
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>