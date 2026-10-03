<?php
// api/users/update_profile.php
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

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$bio = trim($_POST['bio'] ?? '');

try {
    // Ensure bio column exists (best-effort)
    try {
        $pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL');
    } catch (Exception $inner) {
        // ignore if DB does not support IF NOT EXISTS; we'll continue
    }

    $updates = [];
    $params = [];
    if ($firstName !== '') {
        $updates[] = 'first_name = :first_name';
        $params[':first_name'] = $firstName;
    }
    if ($lastName !== '') {
        $updates[] = 'last_name = :last_name';
        $params[':last_name'] = $lastName;
    }
    if ($bio !== '') {
        $updates[] = 'bio = :bio';
        $params[':bio'] = $bio;
    }


    if (count($updates) > 0) {
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    $result = ['user_id' => $userId];

    respond_success($result, 'Profil güncellendi');
} catch (Exception $e) {
    respond_error(500, 'Profil güncellerken hata oluştu', ['exception' => $e->getMessage()]);
}

?>
