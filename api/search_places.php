<?php
// api/search_places.php (legacy)

require_once '../includes/db_connect.php';
require_once '../includes/helpers/response.php';
require_once '../includes/helpers/validate.php';
require_once '../includes/helpers/transform.php';

$q = get_string_query('q');
$limit = get_int_query('limit', 50);

if ($q === '') {
    respond_success([], 'No query');
}

if ($limit < 1) {
    $limit = 50;
}

try {
    // Escape LIKE special characters
    $escaped_q = addcslashes($q, '%_');
    $like = '%' . $escaped_q . '%';

    $cityStmt = $pdo->prepare("SELECT id, name FROM cities WHERE name LIKE :q LIMIT 1");
    $cityStmt->bindValue(':q', $like, PDO::PARAM_STR);
    $cityStmt->execute();
    $matchedCity = $cityStmt->fetch();

    if ($matchedCity) {
        $sql = "
            SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
                   l.city_id, c.name AS city_name
            FROM locations l
            LEFT JOIN cities c ON l.city_id = c.id
            WHERE c.name LIKE :qcity
            ORDER BY l.name
            LIMIT :limit
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':qcity', $like, PDO::PARAM_STR);
    } else {
        $sql = "
            SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
                   l.city_id, c.name AS city_name
            FROM locations l
            LEFT JOIN cities c ON l.city_id = c.id
            WHERE l.name LIKE :q1
               OR c.name LIKE :q2
            ORDER BY l.name
            LIMIT :limit
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':q1', $like, PDO::PARAM_STR);
        $stmt->bindValue(':q2', $like, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll();
    $results = normalize_location_list($results);
    respond_success($results, 'Arama sonuçları');
} catch (Exception $e) {
    respond_error(500, 'Sorgu hatası');
}
?>