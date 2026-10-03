<?php
// api/routes/create.php

require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/validate.php';
require_once '../../includes/helpers/auth.php';

start_session();
ob_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error(405, 'Method not allowed');
}

// CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    respond_error(403, 'Güvenlik doğrulaması başarısız');
}

$userId = current_user_id();
if (!$userId) {
    respond_error(401, 'Oturum açmanız gerekiyor');
}

$routeName = trim((string)($_POST['route_name'] ?? ''));
$isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1;
$cityId = isset($_POST['city_id']) && $_POST['city_id'] !== '' ? (int)$_POST['city_id'] : null;
$stops = $_POST['stops'] ?? [];

if ($routeName === '') {
    respond_error(400, 'Rota adı gerekli');
}

if (!is_array($stops) || count($stops) === 0) {
    respond_error(400, 'En az bir durak seçmelisiniz');
}

// Prevent duplicate route names for the same user
// This index is defined in schema/seed.sql and should already exist in the database.
$duplicateCheck = $pdo->prepare('SELECT 1 FROM routes WHERE user_id = :user_id AND route_name = :route_name LIMIT 1');
$duplicateCheck->bindValue(':user_id', $userId, PDO::PARAM_INT);
$duplicateCheck->bindValue(':route_name', $routeName, PDO::PARAM_STR);
$duplicateCheck->execute();
if ($duplicateCheck->fetch()) {
    respond_error(409, 'Bu isimle zaten bir rota oluşturdunuz');
}

try {
    $pdo->beginTransaction();

    $insert = $pdo->prepare('INSERT INTO routes (user_id, city_id, route_name, is_public, creation_date) VALUES (:user_id, :city_id, :route_name, :is_public, NOW())');
    $insert->bindValue(':user_id', $userId, PDO::PARAM_INT);
    if ($cityId) $insert->bindValue(':city_id', $cityId, PDO::PARAM_INT); else $insert->bindValue(':city_id', null, PDO::PARAM_NULL);
    $insert->bindValue(':route_name', $routeName, PDO::PARAM_STR);
    $insert->bindValue(':is_public', $isPublic ? 1 : 0, PDO::PARAM_INT);
    $insert->execute();

    $routeId = (int)$pdo->lastInsertId();

    $detailStmt = $pdo->prepare('INSERT INTO route_details (route_id, location_id, order_number) VALUES (:route_id, :location_id, :order_number)');
    $order = 1;
    foreach ($stops as $s) {
        $locId = (int)$s;
        $detailStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
        $detailStmt->bindValue(':location_id', $locId, PDO::PARAM_INT);
        $detailStmt->bindValue(':order_number', $order, PDO::PARAM_INT);
        $detailStmt->execute();
        $order++;
    }

    // Save route to user's saved routes (so it appears in profile)
    try {
        $saveStmt = $pdo->prepare('INSERT INTO route_saved (user_id, route_id) VALUES (:user_id, :route_id)');
        $saveStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $saveStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
        $saveStmt->execute();
    } catch (Exception $e) {
        // ignore save errors (e.g., duplicate or missing route_saved support)
    }

    $pdo->commit();

    respond_success(['route_id' => $routeId], 'Rotanız oluşturulmuştur');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond_error(500, 'Rota oluşturulurken hata oluştu', ['exception' => $e->getMessage()]);
}
