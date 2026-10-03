<?php
require_once '../includes/helpers/auth.php';

if (!require_admin()) {
    header('Location: auth.php');
    exit;
}

require_once '../includes/db_connect.php';
require_once '../includes/helpers/auth.php';

$csrf_token = generate_csrf_token();
$page = trim($_GET['page'] ?? 'dashboard');
$action = trim($_GET['action'] ?? '');

// Fetch all data for tables
$cities = [];
$locations = [];
$users = [];
$routes = [];
$currentAdminId = current_user_id();

try {
    $citiesStmt = $pdo->query("SELECT id, name, plate_code, created_at FROM cities ORDER BY name ASC");
    $cities = $citiesStmt->fetchAll();

    $locationsStmt = $pdo->query("SELECT l.id, l.name, l.category, l.city_id, c.name AS city_name FROM locations l 
        LEFT JOIN cities c ON l.city_id = c.id ORDER BY l.name ASC");
    $locations = $locationsStmt->fetchAll();

    $usersStmt = $pdo->query("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $usersStmt->fetchAll();

    $routesStmt = $pdo->query("SELECT r.route_id, r.route_name, r.is_public, r.creation_date,
            r.city_id, c.name AS city_name,
            r.user_id, CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM routes r
        LEFT JOIN cities c ON r.city_id = c.id
        LEFT JOIN users u ON r.user_id = u.id
        ORDER BY r.route_id DESC");
    $routes = $routesStmt->fetchAll();
} catch (Exception $e) {
    // Handle error silently
}
?>
<!DOCTYPE html>
<html class="light" lang="tr">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Anatolia.go - Admin Yönetimi</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
</head>

<body class="bg-gray-50 text-gray-900">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white min-h-screen fixed left-0 top-0">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-8">Admin Panel</h1>
                <nav class="space-y-2">
                    <a href="?page=dashboard"
                        class="<?php echo $page === 'dashboard' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?> px-4 py-2 rounded-lg block flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Gösterge Paneli
                    </a>
                    <a href="?page=cities"
                        class="<?php echo $page === 'cities' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?> px-4 py-2 rounded-lg block flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">location_city</span>
                        Şehirler
                    </a>
                    <a href="?page=locations"
                        class="<?php echo $page === 'locations' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?> px-4 py-2 rounded-lg block flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">place</span>
                        Lokasyonlar
                    </a>
                    <a href="?page=routes"
                        class="<?php echo $page === 'routes' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?> px-4 py-2 rounded-lg block flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">route</span>
                        Rotalar
                    </a>
                    <a href="?page=users"
                        class="<?php echo $page === 'users' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?> px-4 py-2 rounded-lg block flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">group</span>
                        Kullanıcılar
                    </a>
                </nav>
            </div>
            <div class="absolute bottom-6 left-6 right-6 border-t border-gray-700 pt-6">
                <a href="index.php" class="text-sm hover:text-blue-400">← Siteye Dön</a>
                <a href="logout.php" class="text-sm hover:text-red-400 block mt-2">Çıkış Yap</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="ml-64 flex-1 p-8">
            <header class="mb-8">
                <h2 class="text-3xl font-bold">
                    <?php
                    $titles = [
                        'dashboard' => 'Gösterge Paneli',
                        'cities' => 'Şehirler Yönetimi',
                        'locations' => 'Lokasyonlar Yönetimi',
                        'routes' => 'Rotalar Yönetimi',
                        'users' => 'Kullanıcılar Yönetimi'
                    ];
                    echo $titles[$page] ?? 'Yönetim';
                    ?>
                </h2>
            </header>

            <!-- Dashboard -->
            <?php if ($page === 'dashboard'): ?>
                <div class="grid grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-gray-600 font-semibold mb-2">Toplam Kullanıcı</h3>
                        <p class="text-4xl font-bold text-blue-600"><?php echo count($users); ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-gray-600 font-semibold mb-2">Toplam Lokasyon</h3>
                        <p class="text-4xl font-bold text-green-600"><?php echo count($locations); ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-gray-600 font-semibold mb-2">Toplam Şehir</h3>
                        <p class="text-4xl font-bold text-purple-600"><?php echo count($cities); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cities Management -->
            <?php if ($page === 'cities'): ?>
                <div class="mb-6">
                    <button onclick="prepareCityCreate()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span class="material-symbols-outlined">add</span>
                        Yeni Şehir Ekle
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">ID</th>
                                <th class="px-6 py-3 text-left font-semibold">Şehir Adı</th>
                                <th class="px-6 py-3 text-left font-semibold">Plaka Kodu</th>
                                <th class="px-6 py-3 text-left font-semibold">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cities as $city): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($city['id']); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($city['name']); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($city['plate_code']); ?></td>
                                    <td class="px-6 py-3">
                                        <button onclick="editCity(<?php echo (int) $city['id']; ?>)"
                                            class="text-blue-600 hover:underline text-sm">Düzenle</button>
                                        <button onclick="deleteCity(<?php echo (int) $city['id']; ?>)"
                                            class="text-red-600 hover:underline text-sm ml-3">Sil</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($cities)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">Şehir bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Locations Management -->
            <?php if ($page === 'locations'): ?>
                <div class="mb-6">
                    <button onclick="prepareLocationCreate()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span class="material-symbols-outlined">add</span>
                        Yeni Lokasyon Ekle
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">ID</th>
                                <th class="px-6 py-3 text-left font-semibold">Lokasyon Adı</th>
                                <th class="px-6 py-3 text-left font-semibold">Kategori</th>
                                <th class="px-6 py-3 text-left font-semibold">Şehir</th>
                                <th class="px-6 py-3 text-left font-semibold">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $location): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($location['id']); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($location['name']); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($location['category']); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($location['city_name'] ?? '-'); ?></td>
                                    <td class="px-6 py-3">
                                        <button onclick="editLocation(<?php echo (int) $location['id']; ?>)"
                                            class="text-blue-600 hover:underline text-sm">Düzenle</button>
                                        <button onclick="deleteLocation(<?php echo (int) $location['id']; ?>)"
                                            class="text-red-600 hover:underline text-sm ml-3">Sil</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($locations)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Lokasyon bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Routes Management -->
            <?php if ($page === 'routes'): ?>
                <div class="mb-6">
                    <button onclick="prepareRouteCreate()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span class="material-symbols-outlined">add</span>
                        Yeni Rota Ekle
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">ID</th>
                                <th class="px-6 py-3 text-left font-semibold">Rota Adı</th>
                                <th class="px-6 py-3 text-left font-semibold">Şehir</th>
                                <th class="px-6 py-3 text-left font-semibold">Yazar</th>
                                <th class="px-6 py-3 text-left font-semibold">Public</th>
                                <th class="px-6 py-3 text-left font-semibold">Tarih</th>
                                <th class="px-6 py-3 text-left font-semibold">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($routes as $route): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($route['route_id']); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($route['route_name']); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($route['city_name'] ?? '-'); ?></td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($route['author_name'] ?? '-'); ?></td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2 py-1 rounded-full text-sm <?php echo $route['is_public'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $route['is_public'] ? 'Evet' : 'Hayır'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($route['creation_date']); ?></td>
                                    <td class="px-6 py-3">
                                        <button onclick="editRoute(<?php echo (int) $route['route_id']; ?>)"
                                            class="text-blue-600 hover:underline text-sm">Düzenle</button>
                                        <button onclick="deleteRoute(<?php echo (int) $route['route_id']; ?>)"
                                            class="text-red-600 hover:underline text-sm ml-3">Sil</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($routes)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">Rota bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Users Management -->
            <?php if ($page === 'users'): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">ID</th>
                                <th class="px-6 py-3 text-left font-semibold">Ad Soyad</th>
                                <th class="px-6 py-3 text-left font-semibold">E-posta</th>
                                <th class="px-6 py-3 text-left font-semibold">Rol</th>
                                <th class="px-6 py-3 text-left font-semibold">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td class="px-6 py-3">
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                    </td>
                                    <td class="px-6 py-3"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <button
                                            onclick="editUser(<?php echo (int) $user['id']; ?>, '<?php echo htmlspecialchars($user['role']); ?>')"
                                            class="text-blue-600 hover:underline text-sm">Düzenle</button>
                                        <button onclick="deleteUser(<?php echo (int) $user['id']; ?>)"
                                            class="text-red-600 hover:underline text-sm ml-3">Sil</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Kullanıcı bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- City Modal -->
    <div id="cityModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 id="cityModalTitle" class="text-xl font-bold mb-4">Yeni Şehir Ekle</h3>
            <form id="cityForm" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="id" id="cityId">
                <div>
                    <label class="block text-sm font-semibold mb-1">Şehir Adı</label>
                    <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2"
                        placeholder="Ankara" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Plaka Kodu</label>
                    <input type="text" name="plate_code" required class="w-full border rounded-lg px-3 py-2"
                        placeholder="06" />
                </div>
                <p id="cityError" class="text-sm text-red-600"></p>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal('cityModal')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">İptal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Location Modal -->
    <div id="locationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 id="locationModalTitle" class="text-xl font-bold mb-4">Yeni Lokasyon Ekle</h3>
            <form id="locationForm" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="id" id="locationId">
                <div>
                    <label class="block text-sm font-semibold mb-1">Lokasyon Adı</label>
                    <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2"
                        placeholder="Sultanahmet" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Kategori</label>
                    <input type="text" name="category" required class="w-full border rounded-lg px-3 py-2"
                        placeholder="Tarihi Yer" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Şehir</label>
                    <select name="city_id" required class="w-full border rounded-lg px-3 py-2">
                        <option value="">Şehir Seçiniz</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo (int) $city['id']; ?>"><?php echo htmlspecialchars($city['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p id="locationError" class="text-sm text-red-600"></p>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal('locationModal')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">İptal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Route Modal -->
    <div id="routeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 id="routeModalTitle" class="text-xl font-bold mb-4">Yeni Rota Ekle</h3>
            <form id="routeForm" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="route_id" id="routeId">
                <div>
                    <label class="block text-sm font-semibold mb-1">Rota Adı</label>
                    <input type="text" name="route_name" required class="w-full border rounded-lg px-3 py-2"
                        placeholder="Kapadokya Turu" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Şehir</label>
                    <select name="city_id" id="routeCity" required class="w-full border rounded-lg px-3 py-2">
                        <option value="">Şehir Seçiniz</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo (int) $city['id']; ?>"><?php echo htmlspecialchars($city['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Duraklar</label>
                    <div id="routeLocations" class="max-h-48 overflow-y-auto border rounded-lg bg-white p-3">
                        <?php foreach ($locations as $location): ?>
                            <label data-city-id="<?php echo (int) $location['city_id']; ?>"
                                class="flex items-center gap-2 px-2 py-2 rounded hover:bg-gray-50">
                                <input type="checkbox" name="location_ids[]" value="<?php echo (int) $location['id']; ?>"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span><?php echo htmlspecialchars($location['name'] . ' (' . $location['city_name'] . ')'); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Şehir seçtiğinde sadece o şehrin durakları gösterilir; önce
                        seçtiğin diğer şehir durakları da seçili kalır.</p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="routePublic" name="is_public" checked
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <label for="routePublic" class="text-sm font-semibold">Herkese açık</label>
                </div>
                <p id="routeError" class="text-sm text-red-600"></p>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal('routeModal')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">İptal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Kullanıcı Rolü Düzenle</h3>
            <form id="userForm" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="id" id="userId">
                <div>
                    <label class="block text-sm font-semibold mb-1">Rol</label>
                    <select name="role" id="userRole" required class="w-full border rounded-lg px-3 py-2">
                        <option value="user">Kullanıcı</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <p id="userError" class="text-sm text-red-600"></p>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal('userModal')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">İptal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const cityForm = document.getElementById('cityForm');
        const cityIdInput = document.getElementById('cityId');
        const cityModalTitle = document.getElementById('cityModalTitle');
        const locationForm = document.getElementById('locationForm');
        const locationIdInput = document.getElementById('locationId');
        const locationModalTitle = document.getElementById('locationModalTitle');
        const routeForm = document.getElementById('routeForm');
        const routeIdInput = document.getElementById('routeId');
        const routeModalTitle = document.getElementById('routeModalTitle');
        const routeLocationsContainer = document.getElementById('routeLocations');
        const currentAdminId = <?php echo (int) $currentAdminId; ?>;

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            if (modalId === 'cityModal') {
                resetCityForm();
            }
            if (modalId === 'locationModal') {
                resetLocationForm();
            }
        }

        function prepareCityCreate() {
            resetCityForm();
            openModal('cityModal');
        }

        function prepareLocationCreate() {
            resetLocationForm();
            openModal('locationModal');
        }

        function resetCityForm() {
            cityModalTitle.textContent = 'Yeni Şehir Ekle';
            cityIdInput.value = '';
            cityForm.reset();
            document.getElementById('cityError').textContent = '';
        }

        function resetLocationForm() {
            locationModalTitle.textContent = 'Yeni Lokasyon Ekle';
            locationIdInput.value = '';
            locationForm.reset();
            document.getElementById('locationError').textContent = '';
        }

        function updateRouteLocations() {
            if (!routeLocationsContainer) {
                return;
            }
            const selectedCityId = parseInt(routeForm.querySelector('select[name="city_id"]').value, 10);
            routeLocationsContainer.querySelectorAll('label[data-city-id]').forEach(label => {
                const optionCityId = parseInt(label.dataset.cityId, 10);
                const checkbox = label.querySelector('input[type="checkbox"]');
                const isChecked = checkbox && checkbox.checked;

                if (selectedCityId > 0 && optionCityId !== selectedCityId && !isChecked) {
                    label.style.display = 'none';
                } else {
                    label.style.display = 'flex';
                }
            });
        }

        function resetRouteForm() {
            routeModalTitle.textContent = 'Yeni Rota Ekle';
            routeIdInput.value = '';
            routeForm.reset();
            document.getElementById('routePublic').checked = true;
            document.getElementById('routeError').textContent = '';
            updateRouteLocations();
        }

        routeForm?.querySelector('select[name="city_id"]')?.addEventListener('change', () => {
            updateRouteLocations();
        });

        async function fetchJson(url, options) {
            const response = await fetch(url, options);
            const data = await response.json();
            return { response, data };
        }

        // City Management
        cityForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorEl = document.getElementById('cityError');
            errorEl.textContent = '';

            const isEdit = cityIdInput.value.trim() !== '';
            const method = isEdit ? 'PUT' : 'POST';
            const payload = {
                csrf_token: cityForm.querySelector('input[name="csrf_token"]').value,
                name: cityForm.querySelector('input[name="name"]').value,
                plate_code: cityForm.querySelector('input[name="plate_code"]').value
            };

            if (isEdit) {
                payload.id = parseInt(cityIdInput.value, 10);
            }

            try {
                const { response, data } = await fetchJson('../api/admin/cities.php', {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok || data.status === 'error') {
                    errorEl.textContent = data.message || 'Hata oluştu';
                    return;
                }

                location.reload();
            } catch (error) {
                errorEl.textContent = 'Hata oluştu';
            }
        });

        function deleteCity(id) {
            if (!confirm('Bu şehiri silmek istediğinizden emin misiniz?')) return;

            fetch('../api/admin/cities.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    id: id
                })
            }).then(() => location.reload()).catch(e => alert('Hata: ' + e));
        }

        async function editCity(id) {
            try {
                const { response, data } = await fetchJson(`../api/admin/cities.php?id=${id}`, {
                    method: 'GET'
                });

                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Şehir bilgisi alınamadı');
                    return;
                }

                cityIdInput.value = data.data.id;
                cityForm.querySelector('input[name="name"]').value = data.data.name;
                cityForm.querySelector('input[name="plate_code"]').value = data.data.plate_code;
                cityModalTitle.textContent = 'Şehir Düzenle';
                openModal('cityModal');
            } catch (error) {
                alert('Şehir bilgisi alınırken hata oluştu');
            }
        }

        // Location Management
        locationForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorEl = document.getElementById('locationError');
            errorEl.textContent = '';

            const isEdit = locationIdInput.value.trim() !== '';
            const method = isEdit ? 'PUT' : 'POST';
            const payload = {
                csrf_token: locationForm.querySelector('input[name="csrf_token"]').value,
                name: locationForm.querySelector('input[name="name"]').value,
                category: locationForm.querySelector('input[name="category"]').value,
                city_id: parseInt(locationForm.querySelector('select[name="city_id"]').value, 10)
            };

            if (isEdit) {
                payload.id = parseInt(locationIdInput.value, 10);
            }

            try {
                const { response, data } = await fetchJson('../api/admin/locations.php', {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok || data.status === 'error') {
                    errorEl.textContent = data.message || 'Hata oluştu';
                    return;
                }

                location.reload();
            } catch (error) {
                errorEl.textContent = 'Hata oluştu';
            }
        });

        function deleteLocation(id) {
            if (!confirm('Bu lokasyonu silmek istediğinizden emin misiniz?')) return;

            fetch('../api/admin/locations.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    id: id
                })
            }).then(() => location.reload()).catch(e => alert('Hata: ' + e));
        }

        async function editLocation(id) {
            try {
                const { response, data } = await fetchJson(`../api/admin/locations.php?id=${id}`, {
                    method: 'GET'
                });

                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Lokasyon bilgisi alınamadı');
                    return;
                }

                locationIdInput.value = data.data.id;
                locationForm.querySelector('input[name="name"]').value = data.data.name;
                locationForm.querySelector('input[name="category"]').value = data.data.category;
                locationForm.querySelector('select[name="city_id"]').value = data.data.city_id;
                locationModalTitle.textContent = 'Lokasyon Düzenle';
                openModal('locationModal');
            } catch (error) {
                alert('Lokasyon bilgisi alınırken hata oluştu');
            }
        }

        function prepareRouteCreate() {
            resetRouteForm();
            openModal('routeModal');
        }

        routeForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorEl = document.getElementById('routeError');
            errorEl.textContent = '';

            const isEdit = routeIdInput.value.trim() !== '';
            const method = isEdit ? 'PUT' : 'POST';
            const payload = {
                csrf_token: routeForm.querySelector('input[name="csrf_token"]').value,
                route_name: routeForm.querySelector('input[name="route_name"]').value,
                city_id: parseInt(routeForm.querySelector('select[name="city_id"]').value, 10),
                is_public: routeForm.querySelector('#routePublic').checked ? 1 : 0,
                location_ids: Array.from(routeLocationsContainer.querySelectorAll('input[name="location_ids[]"]:checked')).map(input => parseInt(input.value, 10))
            };

            if (isEdit) {
                payload.route_id = parseInt(routeIdInput.value, 10);
            } else {
                payload.user_id = currentAdminId;
            }

            try {
                const { response, data } = await fetchJson('../api/admin/routes.php', {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok || data.status === 'error') {
                    errorEl.textContent = data.message || 'Hata oluştu';
                    return;
                }

                location.reload();
            } catch (error) {
                errorEl.textContent = 'Hata oluştu';
            }
        });

        function deleteRoute(id) {
            if (!confirm('Bu rotayı silmek istediğinizden emin misiniz?')) return;

            fetch('../api/admin/routes.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    route_id: id
                })
            }).then(() => location.reload()).catch(e => alert('Hata: ' + e));
        }

        async function editRoute(id) {
            try {
                const { response, data } = await fetchJson(`../api/admin/routes.php?id=${id}`, {
                    method: 'GET'
                });

                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Rota bilgisi alınamadı');
                    return;
                }

                routeIdInput.value = data.data.route_id;
                routeForm.querySelector('input[name="route_name"]').value = data.data.route_name;
                routeForm.querySelector('select[name="city_id"]').value = data.data.city_id;
                updateRouteLocations();
                routeLocationsContainer.querySelectorAll('input[name="location_ids[]"]').forEach(input => {
                    input.checked = Array.isArray(data.data.location_ids) && data.data.location_ids.includes(parseInt(input.value, 10));
                });
                routeForm.querySelector('#routePublic').checked = data.data.is_public ? true : false;
                routeModalTitle.textContent = 'Rota Düzenle';
                openModal('routeModal');
            } catch (error) {
                alert('Rota bilgisi alınırken hata oluştu');
            }
        }

        // User Management
        document.getElementById('userForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorEl = document.getElementById('userError');
            errorEl.textContent = '';

            try {
                const { response, data } = await fetchJson('../api/admin/users.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: document.querySelector('input[name="csrf_token"]').value,
                        id: parseInt(document.getElementById('userId').value, 10),
                        role: document.getElementById('userRole').value
                    })
                });

                if (!response.ok || data.status === 'error') {
                    errorEl.textContent = data.message || 'Hata oluştu';
                    return;
                }

                location.reload();
            } catch (error) {
                errorEl.textContent = 'Hata oluştu';
            }
        });

        function editUser(id, currentRole) {
            document.getElementById('userId').value = id;
            document.getElementById('userRole').value = currentRole;
            openModal('userModal');
        }

        function deleteUser(id) {
            if (!confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')) return;

            fetch('../api/admin/users.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value,
                    id: id
                })
            }).then(() => location.reload()).catch(e => alert('Hata: ' + e));
        }

        // Close modals when clicking outside
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>