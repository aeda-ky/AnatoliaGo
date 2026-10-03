<?php
// api/get_places.php (legacy)

require_once '../includes/db_connect.php';
require_once '../includes/helpers/response.php';
require_once '../includes/helpers/transform.php';

try {
    $sql = "SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
            l.city_id, c.name AS city_name
        FROM locations l
        LEFT JOIN cities c ON l.city_id = c.id
        ORDER BY l.name";
    $stmt = $pdo->query($sql);
    $places = $stmt->fetchAll();

    $places = normalize_location_list($places);

    respond_success($places, 'Lokasyonlar alındı');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>