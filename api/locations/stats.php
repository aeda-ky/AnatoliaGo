<?php
// api/locations/stats.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$cityId = get_int_query('city_id');
$category = get_string_query('category');
$hasCoords = get_int_query('has_coords');
$hasImage = get_int_query('has_image');

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
    $statsSql = "SELECT
            COUNT(*) AS total_locations,
            COUNT(DISTINCT l.city_id) AS total_cities,
            ROUND(AVG(l.avg_rating), 2) AS avg_rating
        FROM locations l
        $whereSql";

    $statsStmt = $pdo->prepare($statsSql);
    foreach ($params as $key => $value) {
        $statsStmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $statsStmt->execute();

    $stats = $statsStmt->fetch();

    $categorySql = "SELECT l.category, COUNT(*) AS total
        FROM locations l
        $whereSql
        GROUP BY l.category
        ORDER BY total DESC";
    $categoryStmt = $pdo->prepare($categorySql);
    foreach ($params as $key => $value) {
        $categoryStmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $categoryStmt->execute();

    $categories = $categoryStmt->fetchAll();

    respond_success([
        'stats' => $stats,
        'categories' => $categories
    ], 'Location stats fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>