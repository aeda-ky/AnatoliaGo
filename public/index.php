<?php
$pageTitle = 'Anatolia.go - Anasayfa';
$activePage = 'index';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background font-body-md text-body-md antialiased pt-16';
$popularLocations = [];
$masonryLocations = [];
$favoriteLocationIds = [];
$isLoggedIn = false;
$fallbackImage = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80';

function get_image_url($image, $fallback)
{
    $image = trim((string) $image);
    if ($image === '') {
        return $fallback;
    }
    if (preg_match('~^https?://~', $image)) {
        return $image;
    }
    if (str_starts_with($image, '/')) {
        return ltrim($image, '/');
    }
    return 'uploads/' . ltrim($image, '/');
}

try {
    require_once '../includes/db_connect.php';
    require_once '../includes/helpers/auth.php';
    $isLoggedIn = require_auth();

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        || (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_location_id'])) {
        if (!$isLoggedIn) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız', 'data' => []]);
                exit;
            }
            header('Location: auth.php');
            exit;
        }

        $userId = current_user_id();
        $locationId = (int) ($_POST['favorite_location_id'] ?? 0);
        $favoriteAction = null;
        $status = 'success';
        $message = 'Favori durumu güncellendi';

        if ($locationId > 0 && verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS location_favorites (
                favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                location_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY ux_user_location (user_id, location_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $checkStmt = $pdo->prepare('SELECT favorite_id FROM location_favorites WHERE user_id = :user_id AND location_id = :location_id LIMIT 1');
            $checkStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $checkStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $deleteStmt = $pdo->prepare('DELETE FROM location_favorites WHERE user_id = :user_id AND location_id = :location_id');
                $deleteStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $deleteStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
                $deleteStmt->execute();
                $favoriteAction = false;
            } else {
                $insertStmt = $pdo->prepare('INSERT INTO location_favorites (user_id, location_id) VALUES (:user_id, :location_id)');
                $insertStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $insertStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
                $insertStmt->execute();
                $favoriteAction = true;
            }
        } else {
            $status = 'error';
            $message = 'Geçersiz istek veya güvenlik doğrulaması başarısız';
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => $status, 'message' => $message, 'data' => ['favorite' => $favoriteAction, 'location_id' => $locationId]]);
            exit;
        }

        header('Location: index.php');
        exit;
    }

    if ($isLoggedIn) {
        $userId = current_user_id();
        $pdo->exec("CREATE TABLE IF NOT EXISTS location_favorites (
            favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            location_id INT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY ux_user_location (user_id, location_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $favoriteLocationStmt = $pdo->prepare('SELECT location_id FROM location_favorites WHERE user_id = :user_id');
        $favoriteLocationStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $favoriteLocationStmt->execute();
        $favoriteLocationIds = array_column($favoriteLocationStmt->fetchAll(), 'location_id');
    }

    $popularStmt = $pdo->query("SELECT l.id, l.name, l.category, l.avg_rating, l.image,
            c.name AS city_name
        FROM locations l
        LEFT JOIN cities c ON l.city_id = c.id
        ORDER BY l.avg_rating DESC, l.name
        LIMIT 2");
    $popularLocations = $popularStmt->fetchAll();

    $masonryStmt = $pdo->query("SELECT l.id, l.name, l.category, l.avg_rating, l.image,
            c.name AS city_name
        FROM locations l
        LEFT JOIN cities c ON l.city_id = c.id
        ORDER BY l.id DESC");
    $masonryLocations = $masonryStmt->fetchAll();
} catch (Exception $e) {
    $popularLocations = [];
    $masonryLocations = [];
}
$extraHead = <<<HTML
<style>
    .masonry-grid {
        column-count: 1;
        column-gap: 24px;
    }
    @media (min-width: 768px) {
        .masonry-grid {
            column-count: 2;
        }
    }
    @media (min-width: 1024px) {
        .masonry-grid {
            column-count: 3;
        }
    }
    @media (min-width: 1280px) {
        .masonry-grid {
            column-count: 4;
        }
    }
    .masonry-item {
        break-inside: avoid;
        margin-bottom: 24px;
    }
</style>
HTML;
include '../includes/header.php';
?>
<!-- Main Canvas -->
<main class="min-h-screen pb-24">
    <!-- Hero Bento Section -->
    <section class="max-w-[1920px] mx-auto pl-margin-mobile md:pl-margin-desktop pr-0 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-card-gap">
            <!-- Main Hero Graphic/Search -->
            <div
                class="lg:col-span-8 bg-surface-container rounded-xl rounded-r-none overflow-hidden relative shadow-[0_4px_20px_rgba(0,163,177,0.06)] h-[500px]">
                <img alt="Hot air balloons floating over the rocky landscape of Cappadocia, Turkey at sunrise"
                    class="w-full h-full object-cover"
                    data-alt="Hot air balloons floating over the rocky landscape of Cappadocia, Turkey at sunrise with warm golden hour lighting"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuBJ9nNzIMji99RXwpeb9duMQTfRIjFmF8mcAVkEcBVjXEn0bF1bhTRISwvpMuqmRLE349cGQ4AXSHY9G5fdpKIGChXRySIguHcpeESo3HNO5QadU42TMsryG_xa9nSnPH43_5pDG-IwoNXjSE8WARf6_psIZhh5gelF2LEavF-DWHRTjVJE4q8yPRrSKVII7VDsZoflz9dLyL4ykPoSzpYSPBgitLLzCuAGz3ElEFS6dNXp5yF2r4PwtppNG1sJpu9EiRYxgjXKl_M"
                    style="">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full py-8 md:py-12 px-8 md:px-12">
                    <h1 class="font-headline-xl text-headline-xl text-white mb-4" style="">Anadolu'nun Büyüsünü Keşfet
                    </h1>
                    <p class="font-body-lg text-body-lg text-white/90 mb-8 max-w-2xl" style="">Antik kalıntılardan eşsiz
                        kıyılara ve yerel lezzetlere uzanan bir yolculuk.</p>
                    <!-- Large Search -->
                    <form action="explore.php"
                        class="bg-white rounded-lg p-2 flex items-center w-full max-w-3xl mx-auto shadow-lg"
                        method="get">
                        <span class="material-symbols-outlined text-outline ml-4" style="">location_on</span>
                        <input
                            class="flex-1 bg-transparent border-none focus:ring-0 text-on-surface font-body-lg text-body-lg px-4 h-12"
                            name="q" placeholder="Şehir, bölge veya yer ara..." type="text">
                        <button
                            class="bg-primary text-on-primary px-8 h-12 rounded-lg font-label-md text-label-md hover:bg-on-primary-fixed-variant transition-colors shadow-[0_4px_20px_rgba(0,105,114,0.2)]"
                            style="" type="submit">
                            Keşfet
                        </button>
                    </form>
                </div>
            </div>
            <!-- Mini Map / Quick Stats Widget -->
            <div class="lg:col-span-4 flex flex-col gap-card-gap">
                <div
                    class="bg-surface rounded-xl p-6 shadow-[0_4px_20px_rgba(0,163,177,0.06)] flex-1 relative overflow-hidden">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-2 relative z-10" style="">Şu An
                        Popüler</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-6 relative z-10" style="">Bu haftanın
                        en yüksek puanlı yerleri</p>
                    <div class="space-y-4 relative z-10">
                        <?php foreach ($popularLocations as $location): ?>
                            <div class="flex items-center gap-4 bg-white/80 backdrop-blur p-3 rounded-lg shadow-sm border border-surface-container-highest"
                                data-location-id="<?php echo (int) $location['id']; ?>">
                                <img alt="<?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="w-12 h-12 rounded-lg object-cover"
                                    src="<?php echo htmlspecialchars(get_image_url($location['image'] ?? '', $fallbackImage), ENT_QUOTES, 'UTF-8'); ?>"
                                    style="">
                                <div class="flex-1">
                                    <h3 class="font-label-md text-label-md text-on-surface" style=""><a
                                            class="hover:underline"
                                            href="location-detail.php?id=<?php echo (int) $location['id']; ?>"><?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                                    </h3>
                                    <p class="font-body-md text-body-md text-on-surface-variant text-xs" style="">
                                        <?php echo htmlspecialchars($location['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 text-tertiary">
                                    <span class="material-symbols-outlined text-sm"
                                        style="font-variation-settings: &quot;FILL&quot; 1;">star</span>
                                    <span class="font-label-md text-label-md text-xs"
                                        style=""><?php echo htmlspecialchars($location['avg_rating'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Map Background Suggestion -->
                    <div class="absolute inset-x-0 bottom-0 h-32 opacity-20 bg-[url('https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&amp;auto=format&amp;fit=crop&amp;w=800&amp;q=80')] bg-cover bg-bottom mix-blend-multiply"
                        data-alt="Abstract map background showing topographic lines"></div>
                </div>
                <!-- Category Quick Links -->
                <div
                    class="bg-primary-container rounded-xl p-6 shadow-[0_4px_20px_rgba(0,163,177,0.06)] text-on-primary-container">
                    <h2 class="font-headline-md text-headline-md mb-4" style="">Seçilmiş Deneyimler</h2>
                    <?php
                    $currentQ = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
                    function categoryBtnClass($isActive)
                    {
                        if ($isActive) {
                            return 'whitespace-nowrap px-5 py-2 rounded-full bg-primary text-on-primary font-label-md text-label-md transition-colors';
                        }
                        return 'whitespace-nowrap px-5 py-2 rounded-full bg-surface-container-high text-on-surface-variant font-label-md text-label-md hover:bg-surface-container-highest transition-colors';
                    }
                    ?>
                    <div class="flex flex-wrap gap-2">
                        <a href="explore.php?q=Tarihi"
                            class="<?php echo categoryBtnClass($currentQ === 'Tarihi'); ?>">Tarihi</a>
                        <a href="explore.php?q=Doğa"
                            class="<?php echo categoryBtnClass($currentQ === 'Doğa'); ?>">Doğa</a>
                        <a href="explore.php?q=Müze"
                            class="<?php echo categoryBtnClass($currentQ === 'Müze'); ?>">Müze</a>
                        <a href="explore.php?q=Park"
                            class="<?php echo categoryBtnClass($currentQ === 'Park'); ?>">Park</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Filters Strip -->
    <section
        class="max-w-[1920px] mx-auto px-margin-mobile md:px-margin-desktop py-6 flex flex-wrap items-center gap-4 border-b border-surface-container-highest mb-8 sticky top-16 bg-background/80 backdrop-blur z-40">
        <div class="flex gap-3 items-center w-full md:w-auto">
            <div id="home-cat-chips" class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                <button data-cat=""
                    class="filter-chip whitespace-nowrap px-5 py-2 rounded-full font-label-md text-label-md">Tümü</button>
                <button data-cat="Tarihi"
                    class="filter-chip whitespace-nowrap px-5 py-2 rounded-full font-label-md text-label-md">Tarihi</button>
                <button data-cat="Doğa"
                    class="filter-chip whitespace-nowrap px-5 py-2 rounded-full font-label-md text-label-md">Doğa</button>
                <button data-cat="Müze"
                    class="filter-chip whitespace-nowrap px-5 py-2 rounded-full font-label-md text-label-md">Müze</button>
                <button data-cat="Park"
                    class="filter-chip whitespace-nowrap px-5 py-2 rounded-full font-label-md text-label-md">Park</button>
            </div>
        </div>

    </section>
    <!-- Masonry Grid -->
    <section class="max-w-[1920px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="masonry-grid">
            <?php foreach ($masonryLocations as $location): ?>
                <div class="masonry-item bg-surface rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(0,163,177,0.1)] transition-shadow duration-300 group cursor-pointer relative"
                    data-location-id="<?php echo (int) $location['id']; ?>">
                    <img alt="<?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full h-auto object-cover"
                        src="<?php echo htmlspecialchars(get_image_url($location['image'] ?? '', $fallbackImage), ENT_QUOTES, 'UTF-8'); ?>"
                        style="">
                    <div
                        class="absolute top-4 right-4 bg-white/90 p-2 rounded-full shadow-sm <?php echo in_array((int) $location['id'], $favoriteLocationIds, true) ? 'text-secondary' : 'text-surface-variant'; ?> hover:text-secondary transition-colors">
                        <form method="post" action="index.php" class="inline favorite-form">
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES); ?>">
                            <input type="hidden" name="favorite_location_id" value="<?php echo (int) $location['id']; ?>">
                            <button type="submit" class="favorite-toggle material-symbols-outlined"
                                aria-label="Favoriye ekle"
                                style="font-variation-settings: <?php echo in_array((int) $location['id'], $favoriteLocationIds, true) ? "'FILL' 1" : "'FILL' 0"; ?>;"
                                aria-pressed="<?php echo in_array((int) $location['id'], $favoriteLocationIds, true) ? 'true' : 'false'; ?>">
                                favorite
                            </button>
                        </form>
                    </div>
                    <div class="p-4 bg-surface">
                        <h3 class="font-headline-md text-headline-md text-on-surface text-lg mb-1 group-hover:text-primary transition-colors"
                            style=""><a class="hover:underline"
                                href="location-detail.php?id=<?php echo (int) $location['id']; ?>"><?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                        </h3>
                        <div class="flex justify-between items-center mt-2">
                            <div class="flex items-center gap-1 text-on-surface-variant font-label-md text-label-md text-sm"
                                style="">
                                <span class="material-symbols-outlined text-sm" style="">location_on</span>
                                <?php echo htmlspecialchars($location['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="flex items-center gap-1 text-tertiary">
                                <span class="material-symbols-outlined text-sm"
                                    style="font-variation-settings: &quot;FILL&quot; 1;">star</span>
                                <span class="font-label-md text-label-md text-sm"
                                    style=""><?php echo htmlspecialchars($location['avg_rating'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Load More -->
        <div class="flex justify-center mt-12">
            <a href="explore.php"
                class="inline-flex items-center justify-center px-8 py-3 bg-surface text-on-surface border border-outline-variant rounded-lg font-label-md text-label-md hover:bg-surface-variant hover:text-primary transition-colors shadow-sm">
                Daha Fazla İlham
            </a>
        </div>
    </section>
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form.favorite-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(form);
                const button = form.querySelector('button[type="submit"]');
                const locationId = formData.get('favorite_location_id');

                try {
                    const response = await fetch('index.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const responseText = await response.text();

                    if (!contentType.includes('application/json')) {
                        console.error('Non-JSON response:', responseText.substring(0, 500));
                        throw new Error('Sunucudan JSON dışında yanıt geldi');
                    }

                    const data = JSON.parse(responseText);
                    if (!response.ok || data.status !== 'success') {
                        throw new Error(data.message || 'Favori güncellenemedi');
                    }

                    const favorited = data.data?.favorite === true;
                    if (button) {
                        button.setAttribute('aria-pressed', favorited ? 'true' : 'false');
                        button.style.fontVariationSettings = favorited ? '"FILL" 1' : '"FILL" 0';
                        if (favorited) {
                            button.classList.add('text-secondary');
                        } else {
                            button.classList.remove('text-secondary');
                        }
                    }
                } catch (error) {
                    console.error(error);
                    alert(error.message || 'Favori ekleme sırasında hata oluştu.');
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chips = Array.from(document.querySelectorAll('#home-cat-chips .filter-chip'));

        const setChipActive = (chipToActivate) => {
            chips.forEach(c => {
                c.classList.remove('bg-primary', 'text-on-primary', 'bg-surface-container-high', 'text-on-surface-variant');
                if (c === chipToActivate) {
                    c.classList.add('bg-primary', 'text-on-primary');
                } else {
                    c.classList.add('bg-surface-container-high', 'text-on-surface-variant');
                }
            });
        };

        const defaultChip = chips.find(c => !c.dataset.cat) || chips[0];
        setChipActive(defaultChip);

        chips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                e.preventDefault();
                setChipActive(chip);
                navigateToExplore();
            });
        });

        function navigateToExplore() {
            const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const u = new URL(basePath + '/explore.php', window.location.origin);
            const activeChip = chips.find(c => c.classList.contains('bg-primary'));
            if (activeChip) {
                const cat = activeChip.dataset.cat;
                if (cat) u.searchParams.set('q', cat);
            }
            window.location.href = u.toString();
        }
    });
</script>
<?php include '../includes/footer.php'; ?>