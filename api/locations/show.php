<?php
// api/locations/show.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/transform.php';

$id = get_int_query('id');
if (!$id) {
    respond_error(400, 'Geçersiz id');
}

try {
    $sql = "SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
            l.lat, l.lng,
            l.city_id, c.name AS city_name
        FROM locations l
        LEFT JOIN cities c ON l.city_id = c.id
        WHERE l.id = :id
        LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $item = $stmt->fetch();
    if (!$item) {
        respond_error(404, 'Kayıt bulunamadı');
    }

    $item = normalize_location_item($item);

    $ratingsStmt = $pdo->prepare("SELECT rating_id, user_id, star_count, rating_date
        FROM ratings WHERE location_id = :id ORDER BY rating_date DESC");
    $ratingsStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $ratingsStmt->execute();
    $ratings = $ratingsStmt->fetchAll();

    $item['ratings'] = $ratings;

    respond_success($item, 'Location fetched');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>