<?php
$pageTitle = 'Anatolia.go - Rotalar';
$activePage = 'routes';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background antialiased min-h-screen flex flex-col';
$extraHead = <<<HTML
<style>
    .material-symbols-outlined.fill {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
</style>
HTML;

// Initialize variables
$routes = [];
$cities = [];
$cityId = 0;
$q = '';
$sort = 'new';
$favoritedRouteIds = [];
$savedRouteIds = [];
$fallbackImage = 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=80';

// Handle POST requests (must be before any HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        require_once '../includes/db_connect.php';
        require_once '../includes/helpers/auth.php';

        $currentUserId = current_user_id();
        if ($currentUserId) {
            $userExistsStmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
            $userExistsStmt->bindValue(':id', $currentUserId, PDO::PARAM_INT);
            $userExistsStmt->execute();
            if (!$userExistsStmt->fetch()) {
                // Geçersiz oturum kullanıcı kimliği varsa temizle
                unset($_SESSION['user_id'], $_SESSION['user_role']);
                $currentUserId = null;
            }
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $response = ['status' => 'error', 'message' => 'İşlem başarısız oldu', 'data' => []];

        if (!$currentUserId) {
            $response['message'] = 'Giriş yapmalısınız';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            header('Location: auth.php');
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $response['message'] = 'Güvenlik doğrulaması başarısız';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            header('Location: routes.php');
            exit;
        }

        $routeId = isset($_POST['route_id']) ? (int) $_POST['route_id'] : 0;
        $action = isset($_POST['action']) ? (string) $_POST['action'] : '';

        if ($routeId <= 0) {
            $response['message'] = 'Geçersiz rota ID';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            header('Location: routes.php');
            exit;
        }

        // Verify route exists
        $routeStmt = $pdo->prepare('SELECT route_id FROM routes WHERE route_id = :route_id LIMIT 1');
        $routeStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
        $routeStmt->execute();

        if (!$routeStmt->fetch()) {
            $response['message'] = 'Rota bulunamadı';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            header('Location: routes.php');
            exit;
        }

        $active = null;

        if ($action === 'toggle_route_save') {
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
            $checkStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $deleteStmt = $pdo->prepare('DELETE FROM route_saved WHERE user_id = :user_id AND route_id = :route_id');
                $deleteStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
                $deleteStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
                $deleteStmt->execute();
                $active = false;
            } else {
                $insertStmt = $pdo->prepare('INSERT INTO route_saved (user_id, route_id) VALUES (:user_id, :route_id)');
                $insertStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
                $insertStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
                $insertStmt->execute();
                $active = true;
            }
        } elseif ($action === 'toggle_route_favorite') {
            $pdo->exec("CREATE TABLE IF NOT EXISTS route_favorites (
                favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                route_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY ux_user_route (user_id, route_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $checkStmt = $pdo->prepare('SELECT favorite_id FROM route_favorites WHERE user_id = :user_id AND route_id = :route_id LIMIT 1');
            $checkStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
            $checkStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $deleteStmt = $pdo->prepare('DELETE FROM route_favorites WHERE user_id = :user_id AND route_id = :route_id');
                $deleteStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
                $deleteStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
                $deleteStmt->execute();
                $active = false;
            } else {
                $insertStmt = $pdo->prepare('INSERT INTO route_favorites (user_id, route_id) VALUES (:user_id, :route_id)');
                $insertStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
                $insertStmt->bindValue(':route_id', $routeId, PDO::PARAM_INT);
                $insertStmt->execute();
                $active = true;
            }
        } else {
            $response['message'] = 'Geçersiz işlem';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            header('Location: routes.php');
            exit;
        }

        $response = [
            'status' => 'success',
            'message' => 'Rota durumu güncellendi',
            'data' => [
                'action' => $action,
                'route_id' => $routeId,
                'active' => $active,
            ],
        ];

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;

    } catch (Exception $e) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası', 'data' => []]);
            exit;
        }

        header('Location: routes.php');
        exit;
    }
}

// Load page content (GET request)
try {
    require_once '../includes/db_connect.php';
    require_once '../includes/helpers/auth.php';
    require_once '../includes/helpers/transform.php';

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

    $cityId = isset($_GET['city_id']) ? (int) $_GET['city_id'] : 0;
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';

    $citiesStmt = $pdo->query('SELECT id, name FROM cities ORDER BY name');
    $cities = $citiesStmt->fetchAll();

    $where = [];
    $params = [];
    if ($cityId) {
        $where[] = 'r.city_id = :city_id';
        $params[':city_id'] = $cityId;
    }
    if ($q !== '') {
        $where[] = '(r.route_name LIKE :q OR c.name LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $orderSql = $sort === 'old' ? 'r.creation_date ASC' : 'r.creation_date DESC';

    $routesSql = "SELECT r.route_id, r.route_name, r.is_public, r.creation_date,
            c.name AS city_name,
            CONCAT(u.first_name, ' ', u.last_name) AS author_name,
            COUNT(rd.detail_id) AS stops,
            l.image AS cover_image
        FROM routes r
        LEFT JOIN cities c ON r.city_id = c.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN route_details rd ON rd.route_id = r.route_id
        LEFT JOIN route_details rd_first
            ON rd_first.route_id = r.route_id
            AND rd_first.order_number = (
                SELECT MIN(rd_min.order_number)
                FROM route_details rd_min
                WHERE rd_min.route_id = r.route_id
            )
        LEFT JOIN locations l ON l.id = rd_first.location_id
        $whereSql
        GROUP BY r.route_id
        ORDER BY $orderSql";

    $routesStmt = $pdo->prepare($routesSql);
    foreach ($params as $key => $value) {
        $routesStmt->bindValue($key, $value, $key === ':city_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $routesStmt->execute();
    $routes = $routesStmt->fetchAll();

    if ($currentUserId) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS route_saved (
            saved_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            route_id INT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY ux_user_route_saved (user_id, route_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS route_favorites (
            favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            route_id INT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY ux_user_route (user_id, route_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $favStmt = $pdo->prepare('SELECT route_id FROM route_favorites WHERE user_id = :user_id');
        $favStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
        $favStmt->execute();
        $favoritedRouteIds = array_column($favStmt->fetchAll(), 'route_id');

        $saveStmt = $pdo->prepare('SELECT route_id FROM route_saved WHERE user_id = :user_id');
        $saveStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
        $saveStmt->execute();
        $savedRouteIds = array_column($saveStmt->fetchAll(), 'route_id');
    }
} catch (Exception $e) {
    $routes = [];
    $cities = [];
}

include '../includes/header.php';
?>
<!-- Main Content Area -->
<main
    class="flex-grow pt-[100px] pb-16 px-margin-mobile md:px-margin-desktop max-w-[1920px] mx-auto w-full flex flex-col lg:flex-row gap-12">
    <!-- Sidebar Filters -->
    <aside class="w-full lg:w-64 flex-shrink-0">
        <div class="sticky top-[100px] flex flex-col gap-8">
            <div>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-6">Filtreler</h2>
                <!-- Filters Form -->
                <div class="mb-6">
                    <form class="flex flex-col gap-6" method="get">
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                            <input
                                class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg py-3 pl-10 pr-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors placeholder:text-on-surface-variant/50"
                                name="q" placeholder="Rota ara..." type="text"
                                value="<?php echo htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        </div>
                        <div class="grid gap-6">
                            <div>
                                <h3 class="font-label-md text-label-md text-on-surface mb-4">Şehir / Bölge</h3>
                                <div class="flex flex-col gap-3">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input
                                            class="form-radio h-5 w-5 text-primary border-outline-variant focus:ring-primary focus:ring-offset-background bg-surface-container-lowest"
                                            name="city_id" type="radio" value="0" <?php echo ($cityId ?? 0) === 0 ? 'checked' : ''; ?> />
                                        <span
                                            class="font-body-md text-body-md text-on-surface-variant group-hover:text-on-surface transition-colors">Tümü</span>
                                    </label>
                                    <?php foreach ($cities as $city): ?>
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input
                                                class="form-radio h-5 w-5 text-primary border-outline-variant focus:ring-primary focus:ring-offset-background bg-surface-container-lowest"
                                                name="city_id" type="radio" value="<?php echo (int) $city['id']; ?>" <?php echo ($cityId ?? 0) === (int) $city['id'] ? 'checked' : ''; ?> />
                                            <span
                                                class="font-body-md text-body-md text-on-surface-variant group-hover:text-on-surface transition-colors"><?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button
                                class="bg-primary text-on-primary px-4 py-3 rounded-lg font-label-md text-label-md shadow-[0_4px_12px_rgba(0,105,114,0.2)] hover:bg-on-primary-fixed-variant transition-colors"
                                type="submit">Filtrele</button>
                            <a class="inline-flex items-center justify-center bg-surface-container-lowest text-on-surface px-4 py-3 rounded-lg border border-outline-variant hover:bg-surface-container transition-colors"
                                href="routes.php">Temizle</a>
                        </div>
                    </form>
                </div>
    </aside>
    <!-- Route Gallery (Masonry Grid) -->
    <div class="flex-1">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Öne Çıkan Rotalar</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Topluluğun paylaştığı en iyi rotaları
                    keşfet.</p>
            </div>
        </div>
        <!-- Masonry Container -->
        <div class="columns-1 sm:columns-2 xl:columns-3 gap-card-gap space-y-card-gap">
            <?php foreach ($routes as $route): ?>
                <article
                    class="break-inside-avoid bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,105,114,0.06)] hover:shadow-[0_8px_30px_rgba(0,105,114,0.12)] transition-shadow duration-300 overflow-hidden group border border-outline-variant/20"
                    data-route-id="<?php echo (int) $route['route_id']; ?>">
                    <div class="relative w-full aspect-[4/3] overflow-hidden">
                        <?php $coverImage = normalize_image_url($route['cover_image'] ?? null) ?: $fallbackImage; ?>
                        <a class="block" href="route-detail.php?id=<?php echo (int) $route['route_id']; ?>">
                            <img alt="<?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                src="<?php echo htmlspecialchars($coverImage, ENT_QUOTES, 'UTF-8'); ?>" />
                        </a>
                        <div class="absolute top-3 right-3 flex gap-2">
                            <form method="post" action="routes.php" class="inline route-toggle-form"
                                data-action="toggle_route_save">
                                <input type="hidden" name="action" value="toggle_route_save" />
                                <input type="hidden" name="route_id" value="<?php echo (int) $route['route_id']; ?>" />
                                <input type="hidden" name="csrf_token"
                                    value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES); ?>" />
                                <button aria-label="Save Route" type="submit"
                                    class="bg-white/90 backdrop-blur-sm p-2 rounded-full text-on-surface hover:text-secondary transition-colors shadow-sm <?php echo in_array((int) $route['route_id'], $savedRouteIds, true) ? 'text-secondary' : ''; ?>"
                                    aria-pressed="<?php echo in_array((int) $route['route_id'], $savedRouteIds, true) ? 'true' : 'false'; ?>">
                                    <span
                                        class="material-symbols-outlined text-[20px] <?php echo in_array((int) $route['route_id'], $savedRouteIds, true) ? 'fill' : ''; ?>">bookmark</span>
                                </button>
                            </form>
                            <form method="post" action="routes.php" class="inline route-toggle-form"
                                data-action="toggle_route_favorite">
                                <input type="hidden" name="action" value="toggle_route_favorite" />
                                <input type="hidden" name="route_id" value="<?php echo (int) $route['route_id']; ?>" />
                                <input type="hidden" name="csrf_token"
                                    value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES); ?>" />
                                <button aria-label="Favorite Route" type="submit"
                                    class="bg-white/90 backdrop-blur-sm p-2 rounded-full text-on-surface hover:text-error transition-colors shadow-sm <?php echo in_array((int) $route['route_id'], $favoritedRouteIds, true) ? 'text-error' : ''; ?>"
                                    aria-pressed="<?php echo in_array((int) $route['route_id'], $favoritedRouteIds, true) ? 'true' : 'false'; ?>">
                                    <span
                                        class="material-symbols-outlined text-[20px] <?php echo in_array((int) $route['route_id'], $favoritedRouteIds, true) ? 'fill' : ''; ?>">favorite</span>
                                </button>
                            </form>
                        </div>
                        <div
                            class="absolute bottom-3 left-3 bg-secondary/90 backdrop-blur-sm text-on-secondary px-3 py-1 rounded-full font-label-md text-xs tracking-wider">
                            ROTA</div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-1"><a class="hover:underline"
                                href="route-detail.php?id=<?php echo (int) $route['route_id']; ?>"><?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?></a>
                        </h3>
                        <p class="font-label-md text-label-md text-on-surface-variant flex items-center gap-1 mb-4">
                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                            <?php echo htmlspecialchars($route['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?> •
                            <?php echo (int) $route['stops']; ?> Durak
                        </p>
                        <div class="flex items-center justify-between border-t border-outline-variant/30 pt-4 mt-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-headline-md text-xs">
                                    <?php echo htmlspecialchars(mb_substr($route['author_name'] ?? 'A', 0, 1, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <span class="font-body-md text-sm text-on-surface">Oluşturan:
                                    <?php echo htmlspecialchars($route['author_name'] ?? 'Anonim', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span
                                class="font-body-md text-sm text-on-surface-variant"><?php echo htmlspecialchars(date('d.m.Y', strtotime($route['creation_date'])), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form.route-toggle-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(form);
                const button = form.querySelector('button[type="submit"]');
                const actionValue = formData.get('action');
                const routeIdValue = formData.get('route_id');

                try {
                    const response = await fetch('routes.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('Non-JSON response:', text.substring(0, 500));
                        throw new Error('Sunucudan JSON dışında yanıt geldi');
                    }

                    const data = await response.json();
                    if (!response.ok || data.status !== 'success') {
                        throw new Error(data.message || 'Rota durumu güncellenemedi');
                    }

                    const active = data.data?.active === true;
                    const icon = button.querySelector('.material-symbols-outlined');

                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                    if (active) {
                        icon?.classList.add('fill');
                        if (actionValue === 'toggle_route_favorite') {
                            button.classList.add('text-error');
                            button.classList.remove('text-on-surface');
                        } else if (actionValue === 'toggle_route_save') {
                            button.classList.add('text-secondary');
                            button.classList.remove('text-on-surface');
                        }
                    } else {
                        icon?.classList.remove('fill');
                        if (actionValue === 'toggle_route_favorite') {
                            button.classList.remove('text-error');
                            button.classList.add('text-on-surface');
                        } else if (actionValue === 'toggle_route_save') {
                            button.classList.remove('text-secondary');
                            button.classList.add('text-on-surface');
                        }
                    }
                } catch (error) {
                    console.error('Route toggle error:', error);
                    alert(error.message || 'Rota güncellenemedi.');
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>