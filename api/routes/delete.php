<?php
// api/routes/delete.php
require_once '../../includes/db_connect.php';
require_once '../../includes/helpers/response.php';
require_once '../../includes/helpers/auth.php';

start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error(405, 'Method not allowed');
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    respond_error(403, 'Güvenlik doğrulaması başarısız');
}

$userId = current_user_id();
if (!$userId) {
    respond_error(401, 'Oturum açmanız gerekiyor');
}

$routeId = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 0;
if ($routeId <= 0) {
    respond_error(400, 'Geçersiz rota');
}

try {
    // verify ownership
    $check = $pdo->prepare('SELECT user_id FROM routes WHERE route_id = :id LIMIT 1');
    $check->bindValue(':id', $routeId, PDO::PARAM_INT);
    $check->execute();
    $owner = $check->fetchColumn();
    if (!$owner || (int)$owner !== (int)$userId) {
        respond_error(403, 'Bu rotayı silmeye yetkiniz yok');
    }

    $pdo->beginTransaction();

    // delete route_details, route_saved, route_favorites will cascade on FK, but delete to be safe
    $delDetails = $pdo->prepare('DELETE FROM route_details WHERE route_id = :id');
    $delDetails->bindValue(':id', $routeId, PDO::PARAM_INT);
    $delDetails->execute();

    $delSaved = $pdo->prepare('DELETE FROM route_saved WHERE route_id = :id');
    $delSaved->bindValue(':id', $routeId, PDO::PARAM_INT);
    $delSaved->execute();

    $delFav = $pdo->prepare('DELETE FROM route_favorites WHERE route_id = :id');
    $delFav->bindValue(':id', $routeId, PDO::PARAM_INT);
    $delFav->execute();

    $delRoute = $pdo->prepare('DELETE FROM routes WHERE route_id = :id');
    $delRoute->bindValue(':id', $routeId, PDO::PARAM_INT);
    $delRoute->execute();

    $pdo->commit();

    respond_success(['route_id' => $routeId], 'Rotanız silindi');
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond_error(500, 'Rota silinirken hata oluştu', ['exception' => $e->getMessage()]);
}

?>
