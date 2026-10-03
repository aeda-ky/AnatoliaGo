<?php
// api/ratings/stats.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$locationId = get_int_query('location_id');
$cityId = get_int_query('city_id');

$where = [];
$params = [];

if ($locationId) {
    $where[] = 'r.location_id = :location_id';
    $params[':location_id'] = $locationId;
}

if ($cityId) {
    $where[] = 'l.city_id = :city_id';
    $params[':city_id'] = $cityId;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

try {
    $statsSql = "SELECT
            COUNT(*) AS total_ratings,
            ROUND(AVG(r.star_count), 2) AS avg_star
        FROM ratings r
        LEFT JOIN locations l ON r.location_id = l.id
        $whereSql";

    $statsStmt = $pdo->prepare($statsSql);
    foreach ($params as $key => $value) {
        $statsStmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $statsStmt->execute();

    $stats = $statsStmt->fetch();

    $distributionSql = "SELECT r.star_count, COUNT(*) AS total
        FROM ratings r
        LEFT JOIN locations l ON r.location_id = l.id
        $whereSql
        GROUP BY r.star_count
        ORDER BY r.star_count DESC";

    $distributionStmt = $pdo->prepare($distributionSql);
    foreach ($params as $key => $value) {
        $distributionStmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $distributionStmt->execute();

    $distribution = $distributionStmt->fetchAll();

    respond_success([
        'stats' => $stats,
        'distribution' => $distribution
    ], 'Rating stats fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>