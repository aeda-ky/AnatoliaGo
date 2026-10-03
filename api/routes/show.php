<?php
// api/routes/show.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/transform.php';

$id = get_int_query('id');
if (!$id) {
    respond_error(400, 'Geçersiz id');
}

try {
    $sql = "SELECT r.route_id, r.route_name, r.is_public, r.creation_date,
            r.city_id, c.name AS city_name,
            r.user_id, CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM routes r
        LEFT JOIN cities c ON r.city_id = c.id
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.route_id = :id
        LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $route = $stmt->fetch();
    if (!$route) {
        respond_error(404, 'Kayıt bulunamadı');
    }

    $detailsSql = "SELECT rd.detail_id, rd.order_number,
            l.id AS location_id, l.name, l.category, l.description, l.image, l.avg_rating,
            l.lat, l.lng, l.city_id, c.name AS city_name
        FROM route_details rd
        INNER JOIN locations l ON rd.location_id = l.id
        LEFT JOIN cities c ON l.city_id = c.id
        WHERE rd.route_id = :id
        ORDER BY rd.order_number";

    $detailsStmt = $pdo->prepare($detailsSql);
    $detailsStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $detailsStmt->execute();
    $stops = $detailsStmt->fetchAll();
    $route['stops'] = normalize_route_stops($stops);

    respond_success($route, 'Route fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>