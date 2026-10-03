<?php
$pageTitle = 'Anatolia.go - Rota Detayı';
$activePage = 'routes';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background antialiased min-h-screen';
$route = null;
$stops = [];
$fallbackImage = 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=80';
require_once '../includes/helpers/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/helpers/transform.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isSaved = false;
$currentUserId = current_user_id();
if ($currentUserId) {
    $userExistsStmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
    $userExistsStmt->bindValue(':id', $currentUserId, PDO::PARAM_INT);
    $userExistsStmt->execute();
    if (!$userExistsStmt->fetch()) {
        unset($_SESSION['user_id'], $_SESSION['user_role']);
        $currentUserId = null;
    }
}
$statusMessage = '';
$statusType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_route_save') {
    $postRouteId = isset($_POST['route_id']) ? (int) $_POST['route_id'] : 0;
    if ($postRouteId > 0) {
        $id = $postRouteId;
    }

    if ($postRouteId < 1) {
        $statusMessage = 'Geçersiz rota seçimi';
        $statusType = 'error';
    } elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $statusMessage = 'Güvenlik doğrulaması başarısız';
        $statusType = 'error';
    } elseif (!$currentUserId) {
        $statusMessage = 'Kaydetmek için giriş yapmalısınız';
        $statusType = 'error';
    } else {
        $routeStmt = $pdo->prepare('SELECT route_id FROM routes WHERE route_id = :route_id LIMIT 1');
        $routeStmt->bindValue(':route_id', $id, PDO::PARAM_INT);
        $routeStmt->execute();

        if (!$routeStmt->fetch()) {
            $statusMessage = 'Rota bulunamadı';
            $statusType = 'error';
        } else {
            $pdo->exec("CREATE TABLE IF NOT EXISTS route_saved (
                saved_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                route_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY ux_user_route_saved (user_id, route_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $checkStmt = $pdo->prepare('SELECT saved_id FROM route_saved WHERE user_id = :user_id AND route_id = :route_id LIMIT 1');
            $checkStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
            $checkStmt->bindValue(':route_id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $deleteStmt = $pdo->prepare('DELETE FROM route_saved WHERE user_id = :user_id AND route_id = :route_id');
                $deleteStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
                $deleteStmt->bindValue(':route_id', $id, PDO::PARAM_INT);
                $deleteStmt->execute();
                $isSaved = false;
                $statusMessage = 'Rota kaydedilenlerden çıkarıldı';
            } else {
                $insertStmt = $pdo->prepare('INSERT INTO route_saved (user_id, route_id) VALUES (:user_id, :route_id)');
                $insertStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
                $insertStmt->bindValue(':route_id', $id, PDO::PARAM_INT);
                $insertStmt->execute();
                $isSaved = true;
                $statusMessage = 'Rota kaydedildi';
            }
        }
    }

    // Eğer AJAX isteği ise JSON yanıtı döndür ve çık
    $isAjax = (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        $response = [
            'status' => ($statusType === 'error' ? 'error' : 'success'),
            'data' => [
                'route_id' => $id,
                'isSaved' => (bool) $isSaved,
                'message' => $statusMessage
            ]
        ];
        echo json_encode($response);
        exit;
    }
}

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT r.route_id, r.route_name, r.is_public, r.creation_date,
                c.name AS city_name,
                CONCAT(u.first_name, ' ', u.last_name) AS author_name,
                l.image AS cover_image
            FROM routes r
            LEFT JOIN cities c ON r.city_id = c.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN route_details rd_first
                ON rd_first.route_id = r.route_id
                AND rd_first.order_number = (
                    SELECT MIN(rd_min.order_number)
                    FROM route_details rd_min
                    WHERE rd_min.route_id = r.route_id
                )
            LEFT JOIN locations l ON l.id = rd_first.location_id
            WHERE r.route_id = :id
            LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $route = $stmt->fetch();

        if ($route) {
            $route['cover_image_url'] = normalize_image_url($route['cover_image'] ?? null) ?: $fallbackImage;
            if ($currentUserId) {
                $saveCheckStmt = $pdo->prepare('SELECT saved_id FROM route_saved WHERE user_id = :user_id AND route_id = :route_id LIMIT 1');
                $saveCheckStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
                $saveCheckStmt->bindValue(':route_id', $id, PDO::PARAM_INT);
                $saveCheckStmt->execute();
                $isSaved = (bool) $saveCheckStmt->fetch();
            }

            $stopsStmt = $pdo->prepare("SELECT rd.detail_id, rd.order_number,
                    l.id AS location_id, l.name
                FROM route_details rd
                INNER JOIN locations l ON rd.location_id = l.id
                WHERE rd.route_id = :id
                ORDER BY rd.order_number");
            $stopsStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stopsStmt->execute();
            $stops = $stopsStmt->fetchAll();
        }
    } catch (Exception $e) {
        $route = null;
        $stops = [];
    }
}
include '../includes/header.php';
?>

<main class="max-w-[1920px] mx-auto px-margin-mobile md:px-margin-desktop pt-[100px] pb-16">
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-card-gap">
        <div class="lg:col-span-7 bg-surface rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.06)]">
            <?php if ($route): ?>
                <img alt="Rota kapak" class="w-full h-[420px] object-cover"
                    src="<?php echo htmlspecialchars($route['cover_image_url'] ?? $fallbackImage, ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h1 class="font-headline-lg text-headline-lg">
                            <?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </h1>
                        <span class="bg-secondary/10 text-secondary px-3 py-1 rounded-full text-xs">ROTA</span>
                    </div>
                    <p class="text-on-surface-variant mt-3">Topluluk tarafından oluşturulan rota.</p>
                    <div class="flex items-center gap-6 mt-4 text-sm text-on-surface-variant">
                        <span class="flex items-center gap-1"><span
                                class="material-symbols-outlined text-sm">location_on</span>
                            <?php echo htmlspecialchars($route['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><?php echo count($stops); ?> Durak</span>
                        <span><?php echo htmlspecialchars(date('d.m.Y', strtotime($route['creation_date'])), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-6">
                    <h1 class="font-headline-lg text-headline-lg">Rota bulunamadı</h1>
                    <p class="text-on-surface-variant mt-3">Geçersiz veya eksik rota ID.</p>
                </div>
            <?php endif; ?>
        </div>
        <aside class="lg:col-span-5 space-y-6">
            <div class="bg-surface-container-lowest rounded-xl p-6 shadow-[0_4px_20px_rgba(0,105,114,0.06)]">
                <h2 class="font-headline-md text-headline-md mb-4">Rota Bilgisi</h2>
                <div class="space-y-3 text-sm text-on-surface-variant">
                    <p><strong class="text-on-surface">Oluşturan:</strong>
                        <?php echo htmlspecialchars($route['author_name'] ?? 'Anonim', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong class="text-on-surface">Paylaşım:</strong>
                        <?php echo $route ? htmlspecialchars(date('d.m.Y', strtotime($route['creation_date'])), ENT_QUOTES, 'UTF-8') : '-'; ?>
                    </p>
                    <p><strong class="text-on-surface">Durak:</strong> <?php echo count($stops); ?></p>
                </div>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <form method="post" id="route-save-form" class="flex-1">
                        <input type="hidden" name="action" value="toggle_route_save" />
                        <input type="hidden" name="route_id"
                            value="<?php echo $route ? (int) $route['route_id'] : 0; ?>" />
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES); ?>" />
                        <button type="submit"
                            class="w-full bg-primary text-on-primary py-3 rounded-lg font-semibold flex items-center justify-center gap-2 hover:bg-on-primary-fixed-variant transition-colors">
                            <span
                                class="material-symbols-outlined text-on-primary <?php echo $isSaved ? 'fill' : ''; ?>">bookmark</span>
                            <span class="route-save-label"><?php echo $isSaved ? 'Kaydedildi' : 'Kaydet'; ?></span>
                        </button>
                    </form>
                    <button id="route-share-button" type="button"
                        class="flex-1 border border-outline-variant py-3 rounded-lg">Paylaş</button>
                </div>
                <?php if ($statusMessage): ?>
                    <p id="route-save-status"
                        class="mt-3 text-sm <?php echo $statusType === 'error' ? 'text-error' : 'text-primary'; ?>">
                        <?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-6 shadow-[0_4px_20px_rgba(0,105,114,0.06)]">
                <h2 class="font-headline-md text-headline-md mb-4">Duraklar</h2>
                <?php if ($route && count($stops) > 0): ?>
                    <ol class="space-y-3 text-on-surface">
                        <?php foreach ($stops as $stop): ?>
                            <li class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 rounded-full bg-primary text-white text-xs flex items-center justify-center"><?php echo (int) $stop['order_number']; ?></span>
                                <a class="hover:underline"
                                    href="location-detail.php?id=<?php echo (int) $stop['location_id']; ?>"><?php echo htmlspecialchars($stop['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php else: ?>
                    <p class="text-on-surface-variant">Durak bulunamadı.</p>
                <?php endif; ?>
            </div>
        </aside>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = '<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES); ?>';

        const shareButton = document.querySelector('#route-share-button');
        if (shareButton) {
            shareButton.addEventListener('click', async () => {
                const shareUrl = window.location.href;
                const routeName = '<?php echo htmlspecialchars($route['route_name'] ?? 'Rota', ENT_QUOTES); ?>';
                const shareText = `${routeName} rotasını keşfetmek için bu bağlantıyı kullanabilirsiniz.`;

                if (navigator.share) {

                    // AJAX ile kaydet/çıkart işlemi
                    const saveForm = document.getElementById('route-save-form');
                    if (saveForm) {
                        saveForm.addEventListener('submit', async (e) => {
                            e.preventDefault();
                            const submitButton = saveForm.querySelector('button[type="submit"]');
                            const labelSpan = submitButton ? submitButton.querySelector('.route-save-label') : null;
                            const iconSpan = submitButton ? submitButton.querySelector('.material-symbols-outlined') : null;
                            const statusEl = document.getElementById('route-save-status');

                            const formData = new FormData(saveForm);
                            try {
                                const res = await fetch('route-detail.php', {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                });

                                const data = await res.json();
                                if (data && data.status === 'success') {
                                    const isSaved = !!(data.data && data.data.isSaved);
                                    if (labelSpan) labelSpan.textContent = isSaved ? 'Kaydedildi' : 'Kaydet';
                                    if (iconSpan) iconSpan.classList.toggle('fill', isSaved);
                                    if (statusEl) {
                                        statusEl.textContent = data.data.message || '';
                                        statusEl.className = 'mt-3 text-sm ' + (data.status === 'error' ? 'text-error' : 'text-primary');
                                    } else if (data.data.message) {
                                        const p = document.createElement('p');
                                        p.id = 'route-save-status';
                                        p.className = 'mt-3 text-sm ' + (data.status === 'error' ? 'text-error' : 'text-primary');
                                        p.textContent = data.data.message;
                                        saveForm.parentElement.appendChild(p);
                                    }
                                } else {
                                    const msg = data && data.data && data.data.message ? data.data.message : 'İşlem başarısız';
                                    alert(msg);
                                }
                            } catch (err) {
                                console.error(err);
                                alert('İstek sırasında hata oluştu. Lütfen tekrar deneyin.');
                            }
                        });
                    }
                    try {
                        await navigator.share({
                            title: routeName,
                            text: shareText,
                            url: shareUrl
                        });
                        return;
                    } catch (shareError) {
                        console.warn('Native paylaşım desteklenmiyor veya iptal edildi.', shareError);
                    }
                }

                try {
                    await navigator.clipboard.writeText(shareUrl);
                    alert('Rota bağlantısı panoya kopyalandı.');
                } catch (clipboardError) {
                    console.error(clipboardError);
                    prompt('Bu bağlantıyı paylaşabilirsiniz:', shareUrl);
                }
            });
        }
    });
</script>
<?php include '../includes/footer.php'; ?>