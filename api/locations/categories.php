<?php
// api/locations/categories.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';

$limit = get_int_query('limit', 50);
$q = get_string_query('q');

if ($limit < 1) {
    $limit = 50;
}

$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE l.category LIKE :q';
    $params[':q'] = '%' . $q . '%';
}

try {
    $sql = "SELECT l.category, COUNT(*) AS total
        FROM locations l
        $where
        GROUP BY l.category
        ORDER BY total DESC, l.category ASC
        LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll();

    respond_success($items, 'Categories fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>