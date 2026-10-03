<?php
$pageTitle = 'Anatolia.go - Keşfet';
$activePage = 'explore';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background antialiased min-h-screen';
include '../includes/header.php';
?>

<?php
require_once '../includes/db_connect.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$searchResults = [];
$exploreRoutes = [];
$cities = [];

$citiesStmt = $pdo->query('SELECT id, name FROM cities ORDER BY name');
$cities = $citiesStmt->fetchAll();

// Build locations query with category and city filters
$locationsWhere = [];
$locationsParams = [];

// Search query filter across name, city, category, and description
if ($query !== '') {
    $locationsWhere[] = '(l.name LIKE :q_name OR c.name LIKE :q_city OR l.category LIKE :q_category OR l.description LIKE :q_description)';
    $locationsParams[':q_name'] = '%' . $query . '%';
    $locationsParams[':q_city'] = '%' . $query . '%';
    $locationsParams[':q_category'] = '%' . $query . '%';
    $locationsParams[':q_description'] = '%' . $query . '%';
}

// City filter
if ($cityId) {
    $locationsWhere[] = 'l.city_id = :city_id';
    $locationsParams[':city_id'] = $cityId;
}

// Determine sort order
$locationsOrder = 'l.created_at DESC';
if ($sort === 'old') {
    $locationsOrder = 'l.created_at ASC';
} elseif ($sort === 'rating') {
    $locationsOrder = 'COALESCE(l.avg_rating, 0) DESC, l.created_at DESC';
}

// Build WHERE clause
$locationsWhereClause = '';
if (!empty($locationsWhere)) {
    $locationsWhereClause = 'WHERE ' . implode(' AND ', $locationsWhere);
}

$locationsSql = "
    SELECT l.id, l.name, l.category, l.description, l.avg_rating, l.created_at, c.name AS city
    FROM locations l
    LEFT JOIN cities c ON l.city_id = c.id
    $locationsWhereClause
    ORDER BY $locationsOrder
    LIMIT 50
";

$locationsStmt = $pdo->prepare($locationsSql);
foreach ($locationsParams as $key => $value) {
    $locationsStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$locationsStmt->execute();
$searchResults = $locationsStmt->fetchAll();

$where = [];
$params = [];
if ($cityId) {
    $where[] = 'r.city_id = :city_id';
    $params[':city_id'] = $cityId;
}
$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$orderSql = 'r.creation_date DESC';
if ($sort === 'old') {
    $orderSql = 'r.creation_date ASC';
} elseif ($sort === 'rating') {
    // For routes, also sort by creation_date DESC (since routes table has no rating column)
    $orderSql = 'r.creation_date DESC';
}

$routesSql = "SELECT r.route_id, r.route_name, r.creation_date,
        c.name AS city_name,
        CONCAT(u.first_name, ' ', u.last_name) AS author_name,
        COUNT(rd.detail_id) AS stops
    FROM routes r
    LEFT JOIN cities c ON r.city_id = c.id
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN route_details rd ON rd.route_id = r.route_id
    $whereSql
    GROUP BY r.route_id
    ORDER BY $orderSql
    LIMIT 20";

$routesStmt = $pdo->prepare($routesSql);
foreach ($params as $key => $value) {
    $routesStmt->bindValue($key, $value, PDO::PARAM_INT);
}
$routesStmt->execute();
$exploreRoutes = $routesStmt->fetchAll();
?>

    <main class="max-w-[1920px] mx-auto px-margin-mobile md:px-margin-desktop pt-[100px] pb-16">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h1 class="font-headline-lg text-headline-lg">Keşfet</h1>
                <p class="text-on-surface-variant">Yerleşim alanlarını keşfet ve filtrele.</p>
            </div>
            <form class="flex gap-2" method="get">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>">
                <select class="bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2" name="city_id">
                    <option value="">Şehir</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo (int)$city['id']; ?>" <?php echo $cityId === (int)$city['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2" name="sort">
                    <option value="new" <?php echo $sort === 'new' ? 'selected' : ''; ?>>En Yeni</option>
                    <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>En Yüksek Puan</option>
                    <option value="old" <?php echo $sort === 'old' ? 'selected' : ''; ?>>En Eski</option>
                </select>
                <button class="bg-primary text-on-primary px-4 rounded-lg" type="submit">Filtrele</button>
            </form>
        </div>

        <section class="mb-8">
            <?php if (count($searchResults) === 0): ?>
                <div class="bg-surface rounded-xl p-6 text-on-surface-variant text-center">
                    Filtrelerinize uygun mekan bulunamadı.
                </div>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($searchResults as $place): ?>
                        <article class="bg-surface rounded-xl p-6 shadow-[0_4px_20px_rgba(0,105,114,0.06)]">
                            <h3 class="font-headline-md text-headline-md">
                                <a class="hover:underline" href="location-detail.php?id=<?php echo (int)$place['id']; ?>">
                                    <?php echo htmlspecialchars($place['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h3>
                            <p class="text-on-surface-variant mt-2">
                                <?php echo htmlspecialchars($place['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <div class="flex gap-4 mt-4 text-sm text-on-surface-variant">
                                <span><?php echo htmlspecialchars($place['city'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?></span>
                                <span><?php echo htmlspecialchars($place['category'] ?? 'Diğer', ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if ($place['avg_rating']): ?>
                                    <span>⭐ <?php echo number_format($place['avg_rating'], 1); ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const citySelect = document.querySelector('select[name="city_id"]');
            const sortSelect = document.querySelector('select[name="sort"]');
            
            if (citySelect) citySelect.addEventListener('change', (e) => {
                e.target.form.submit();
            });
            if (sortSelect) sortSelect.addEventListener('change', (e) => {
                e.target.form.submit();
            });
        });
    </script>

<?php include '../includes/footer.php'; ?>
