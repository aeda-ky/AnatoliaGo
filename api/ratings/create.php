<?php
// api/ratings/create.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/auth.php';

// Check if user is logged in
if (!require_auth()) {
    respond_error(401, 'Puan vermek için giriş yapmalısınız');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond_error(400, 'Geçersiz JSON');
}

// Verify CSRF token
if (!verify_csrf_token($input['csrf_token'] ?? '')) {
    respond_error(403, 'Güvenlik doğrulaması başarısız');
}

$userId = current_user_id();
$locationId = (int) ($input['location_id'] ?? 0);
$starCount = (int) ($input['star_count'] ?? 0);

// Validation
if ($locationId < 1) {
    respond_error(422, 'Geçersiz lokasyon');
}

if ($starCount < 1 || $starCount > 5) {
    respond_error(422, 'Puan 1-5 arasında olmalı');
}

try {
    // Check if location exists
    $locStmt = $pdo->prepare('SELECT id FROM locations WHERE id = :id LIMIT 1');
    $locStmt->bindValue(':id', $locationId, PDO::PARAM_INT);
    $locStmt->execute();
    if (!$locStmt->fetch()) {
        respond_error(404, 'Lokasyon bulunamadı');
    }

    // Check if user already rated this location
    $checkStmt = $pdo->prepare('SELECT rating_id FROM ratings WHERE location_id = :location_id AND user_id = :user_id LIMIT 1');
    $checkStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
    $checkStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        respond_error(409, 'Bu lokasyona zaten puan vermişsiniz');
    }

    // Insert rating and update average in a transaction
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO ratings (location_id, user_id, star_count)
        VALUES (:location_id, :user_id, :star_count)");
    $stmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':star_count', $starCount, PDO::PARAM_INT);
    $stmt->execute();

    $ratingId = (int) $pdo->lastInsertId();

    $ratingsStmt = $pdo->prepare('SELECT star_count FROM ratings WHERE location_id = :location_id');
    $ratingsStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
    $ratingsStmt->execute();
    $ratings = array_map('intval', $ratingsStmt->fetchAll(PDO::FETCH_COLUMN));

    $totalRatings = count($ratings);
    $sumRatings = $totalRatings > 0 ? array_sum($ratings) : 0;
    $avg = $totalRatings > 0 ? round($sumRatings / $totalRatings, 2) : null;

    $updateStmt = $pdo->prepare('UPDATE locations SET avg_rating = :avg_rating WHERE id = :location_id');
    $updateStmt->bindValue(':avg_rating', $avg);
    $updateStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
    $updateStmt->execute();

    $pdo->commit();

    respond_success([
        'rating_id' => $ratingId,
        'star_count' => $starCount,
        'avg_rating' => $avg,
        'rating_count' => $totalRatings,
        'rating_sum' => $sumRatings
    ], 'Puan başarıyla kaydedildi');

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond_error(500, 'Puan kaydedilirken bir hata oluştu');
}
?>