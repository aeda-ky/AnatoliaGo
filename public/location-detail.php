<?php
$pageTitle = 'Anatolia.go - Konum Detayı';
$activePage = '';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background antialiased min-h-screen';
$location = null;
$imageUrl = null;
$fallbackImage = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

require_once '../includes/helpers/auth.php';
require_once '../includes/helpers/transform.php';
$isLoggedIn = current_user_id() !== null;
$currentUserId = current_user_id();
$isLocationFavorited = false;

// Generate CSRF token
$csrf_token = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_location_id'])) {
    if (!$isLoggedIn) {
        header('Location: auth.php');
        exit;
    }

    $locationId = (int) ($_POST['favorite_location_id'] ?? 0);
    if ($locationId > 0 && verify_csrf_token($_POST['csrf_token'] ?? '')) {
        require_once '../includes/db_connect.php';
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
        $checkStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
        $checkStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            $deleteStmt = $pdo->prepare('DELETE FROM location_favorites WHERE user_id = :user_id AND location_id = :location_id');
            $deleteStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
            $deleteStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
            $deleteStmt->execute();
        } else {
            $insertStmt = $pdo->prepare('INSERT INTO location_favorites (user_id, location_id) VALUES (:user_id, :location_id)');
            $insertStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
            $insertStmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
            $insertStmt->execute();
        }
    }

    header('Location: location-detail.php?id=' . $id);
    exit;
}

if ($id > 0) {
    try {
        require_once '../includes/db_connect.php';
        $stmt = $pdo->prepare("SELECT l.id, l.name, l.category, l.description, l.image, l.avg_rating,
                c.name AS city_name
            FROM locations l
            LEFT JOIN cities c ON l.city_id = c.id
            WHERE l.id = :id
            LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $location = $stmt->fetch();
        if ($location) {
            $imageUrl = normalize_image_url($location['image'] ?? null) ?: $fallbackImage;
        }

        if ($isLoggedIn && $location) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS location_favorites (
                favorite_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                location_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY ux_user_location (user_id, location_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $favCheckStmt = $pdo->prepare('SELECT favorite_id FROM location_favorites WHERE user_id = :user_id AND location_id = :location_id LIMIT 1');
            $favCheckStmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
            $favCheckStmt->bindValue(':location_id', $location['id'], PDO::PARAM_INT);
            $favCheckStmt->execute();
            $isLocationFavorited = (bool) $favCheckStmt->fetch();
        }
    } catch (Exception $e) {
        $location = null;
    }
}
include '../includes/header.php';
?>

<main class="max-w-5xl mx-auto px-6 pt-[100px] pb-16">
    <div class="bg-surface rounded-xl overflow-hidden shadow-[0_8px_30px_rgba(0,105,114,0.08)]">
        <?php if ($location): ?>
            <img alt="<?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?>"
                class="w-full h-[360px] object-cover"
                src="<?php echo htmlspecialchars($imageUrl ?? $fallbackImage, ENT_QUOTES, 'UTF-8'); ?>" />
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h1 class="font-headline-lg text-headline-lg">
                        <?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <div class="flex items-center gap-1 text-tertiary">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span
                            id="location-avg-rating"><?php echo htmlspecialchars($location['avg_rating'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
                <p class="text-on-surface-variant mt-2">
                    <?php echo htmlspecialchars($location['description'] ?? 'Açıklama bulunamadı.', ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="text-on-surface-variant mt-2 text-sm">
                    <?php echo htmlspecialchars($location['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?> •
                    <?php echo htmlspecialchars($location['category'] ?? 'Diğer', ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <?php if ($isLoggedIn && $location): ?>
                    <form method="post" class="mt-4 inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">
                        <input type="hidden" name="favorite_location_id" value="<?php echo (int) $location['id']; ?>">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg border px-5 py-3 font-semibold transition-colors <?php echo $isLocationFavorited ? 'bg-secondary text-on-secondary border-secondary' : 'bg-surface text-on-surface border-outline-variant hover:bg-surface-variant'; ?>"
                            aria-pressed="<?php echo $isLocationFavorited ? 'true' : 'false'; ?>">
                            <span class="material-symbols-outlined"
                                style="font-variation-settings: <?php echo $isLocationFavorited ? "'FILL' 1" : "'FILL' 0"; ?>;">favorite</span>
                            <span
                                class="favorite-label"><?php echo $isLocationFavorited ? 'Favorilerden Çıkar' : 'Favorilere Ekle'; ?></span>
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="p-6">
                    <h1 class="font-headline-lg text-headline-lg">Konum bulunamadı</h1>
                    <p class="text-on-surface-variant mt-2">Geçersiz veya eksik konum ID.</p>
                </div>
            <?php endif; ?>

            <div class="mt-6">
                <h2 class="font-headline-md text-headline-md mb-4">Puanla</h2>
                <?php if ($isLoggedIn): ?>
                    <form id="rating-form" class="space-y-4">
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">
                        <input type="hidden" name="location_id" value="<?php echo (int) $location['id']; ?>">
                        <div class="flex gap-3">
                            <div class="flex gap-2 text-3xl">
                                <button
                                    class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-tertiary transition-colors"
                                    data-star="1" type="button" style="font-variation-settings: 'FILL' 0;">star</button>
                                <button
                                    class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-tertiary transition-colors"
                                    data-star="2" type="button" style="font-variation-settings: 'FILL' 0;">star</button>
                                <button
                                    class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-tertiary transition-colors"
                                    data-star="3" type="button" style="font-variation-settings: 'FILL' 0;">star</button>
                                <button
                                    class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-tertiary transition-colors"
                                    data-star="4" type="button" style="font-variation-settings: 'FILL' 0;">star</button>
                                <button
                                    class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-tertiary transition-colors"
                                    data-star="5" type="button" style="font-variation-settings: 'FILL' 0;">star</button>
                            </div>
                            <input hidden id="rating-input" name="star_count" type="text" value="0" />
                            <span id="rating-text" class="text-sm text-on-surface-variant">Puan seçiniz</span>
                        </div>
                        <button class="bg-tertiary text-white px-6 py-2 rounded-lg font-semibold" type="submit">Puanı
                            Kaydet</button>
                        <p class="text-sm text-error" id="rating-error" role="alert"></p>
                    </form>
                <?php else: ?>
                    <p class="text-on-surface-variant text-sm">Puan vermek için lütfen <a class="text-primary font-semibold"
                            href="auth.php">giriş yapın</a>.</p>
                <?php endif; ?>
            </div>

            <?php if ($location): ?>
                <div class="mt-6 flex gap-3">
                    <a class="bg-primary text-on-primary px-6 py-3 rounded-lg" href="routes.php">Rotaya Ekle</a>
                    <a class="border border-outline-variant px-6 py-3 rounded-lg"
                        href="map.php?location=<?php echo (int) $location['id']; ?>">Haritada Gör</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    <?php if ($isLoggedIn): ?>
        const ratingForm = document.getElementById('rating-form');
        const ratingInput = document.getElementById('rating-input');
        const ratingText = document.getElementById('rating-text');
        const ratingError = document.getElementById('rating-error');
        const starButtons = document.querySelectorAll('button[data-star]');
        const favoriteButton = document.getElementById('location-favorite-button');

        starButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const star = parseInt(button.dataset.star);
                ratingInput.value = star;

                // Visual feedback
                starButtons.forEach((btn, idx) => {
                    if (idx < star) {
                        btn.style.setProperty('font-variation-settings', "'FILL' 1");
                        btn.classList.remove('text-on-surface-variant');
                        btn.classList.add('text-tertiary');
                    } else {
                        btn.style.setProperty('font-variation-settings', "'FILL' 0");
                        btn.classList.add('text-on-surface-variant');
                        btn.classList.remove('text-tertiary');
                    }
                });

                ratingText.textContent = star + ' yıldız';
            });

            button.addEventListener('mouseover', (e) => {
                const star = parseInt(button.dataset.star);
                starButtons.forEach((btn, idx) => {
                    if (idx < star) {
                        btn.style.opacity = '1';
                    } else {
                        btn.style.opacity = '0.5';
                    }
                });
            });

            button.addEventListener('mouseout', () => {
                starButtons.forEach(btn => btn.style.opacity = '1');
            });
        });

        ratingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            ratingError.textContent = '';

            const rating = parseInt(ratingInput.value);
            if (rating < 1 || rating > 5) {
                ratingError.textContent = 'Lütfen 1-5 arasında bir puan seçiniz.';
                return;
            }

            try {
                const response = await fetch('../api/ratings/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: ratingForm.querySelector('input[name="csrf_token"]').value,
                        location_id: parseInt(ratingForm.querySelector('input[name="location_id"]').value),
                        star_count: rating
                    })
                });

                const data = await response.json();
                if (!response.ok || data.status === 'error') {
                    ratingError.textContent = data.message || 'Puan kaydedilirken bir hata oluştu.';
                    return;
                }

                ratingError.classList.remove('text-error');
                ratingError.classList.add('text-success');
                ratingError.textContent = 'Puanınız kaydedildi! Teşekkür ederiz.';
                const avgRatingEl = document.getElementById('location-avg-rating');
                if (avgRatingEl && data.avg_rating !== undefined) {
                    avgRatingEl.textContent = Number(data.avg_rating).toFixed(2);
                }
                ratingForm.reset();
                starButtons.forEach(btn => {
                    btn.style.setProperty('font-variation-settings', "'FILL' 0");
                    btn.classList.add('text-on-surface-variant');
                    btn.classList.remove('text-tertiary');
                });
                ratingText.textContent = 'Puan seçiniz';
            } catch (error) {
                ratingError.textContent = 'Bağlantı hatası. Lütfen tekrar dene.';
            }
        });
    <?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>