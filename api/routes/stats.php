<?php
// api/routes/stats.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$cityId = get_int_query('city_id');
$publicOnly = get_int_query('is_public', 1);

$where = [];
$params = [];

if ($cityId) {
    $where[] = 'r.city_id = :city_id';
    $params[':city_id'] = $cityId;
}

if ($publicOnly !== null) {
    $where[] = 'r.is_public = :is_public';
    $params[':is_public'] = $publicOnly ? 1 : 0;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

try {
    $statsSql = "SELECT
            COUNT(DISTINCT r.route_id) AS total_routes,
            COUNT(DISTINCT r.user_id) AS total_authors,
            COUNT(DISTINCT rd.detail_id) AS total_stops,
            ROUND(AVG(stop_counts.stops_per_route), 2) AS avg_stops_per_route
        FROM routes r
        LEFT JOIN route_details rd ON rd.route_id = r.route_id
        LEFT JOIN (
            SELECT rd2.route_id, COUNT(*) AS stops_per_route
            FROM route_details rd2
            GROUP BY rd2.route_id
        ) stop_counts ON stop_counts.route_id = r.route_id
        $whereSql";

    $statsStmt = $pdo->prepare($statsSql);
    foreach ($params as $key => $value) {
        $statsStmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $statsStmt->execute();

    $stats = $statsStmt->fetch();

    respond_success($stats, 'Route stats fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>