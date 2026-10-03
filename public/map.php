<?php
$pageTitle = 'Anatolia.go - Harita';
$activePage = 'map';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background font-body-md min-h-screen flex flex-col antialiased';
$extraHead = <<<HTML
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
    #map {
        height: 100%;
        width: 100%;
    }
    .map-card-image {
        height: 180px;
    }
    .map-card-image img {
        height: 100%;
        width: 100%;
        object-fit: cover;
        display: block;
    }
    .map-card-placeholder {
        background: linear-gradient(135deg, rgba(0,105,114,0.12), rgba(0,105,114,0.04));
    }
    .map-card-placeholder .material-symbols-outlined {
        font-size: 32px;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    /* City select: remove native arrow, add custom arrow and spacing to avoid overlap */
    #city-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 2.4rem;
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        background-size: 1rem;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23006562' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
    }
    .selected {
        box-shadow: 0 6px 24px rgba(0,105,114,0.08);
        border: 2px solid rgba(0,105,114,0.12);
        transform: translateY(-4px);
    }
    .route-stop-label {
        background: #ff6b24 !important;
        color: #fff !important;
        padding: 4px 6px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12) !important;
    }
    .map-highlight {
        border: 2px solid #ff6b24;
        box-shadow: 0 16px 40px rgba(255,107,36,0.18);
        transform: translateY(-2px);
    }
</style>
HTML;
include '../includes/header.php';
require_once '../includes/db_connect.php';
$currentUserFirstName = '';
if ($isLoggedIn) {
    $stmt = $pdo->prepare('SELECT first_name FROM users WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', current_user_id(), PDO::PARAM_INT);
    $stmt->execute();
    $userRow = $stmt->fetch();
    $currentUserFirstName = $userRow['first_name'] ?? '';
}
?>
<!-- Main Content Area -->
<main class="flex-grow flex pt-16 h-screen overflow-hidden">
<!-- Side Panel (Location Details) -->
<aside class="w-full md:w-96 lg:w-[450px] bg-surface-container-lowest border-r border-outline-variant flex flex-col shadow-[4px_0_20px_rgba(0,105,114,0.06)] z-10 flex-shrink-0">
<!-- Search & Filter Header -->
<div class="p-6 border-b border-surface-variant bg-surface-container-lowest">
    <div class="mb-3">
        <h1 id="map-heading" class="font-headline-lg text-headline-lg text-on-surface truncate" title="Tüm Şehirler Keşfi">Tüm Şehirler Keşfi</h1>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex items-center gap-4 w-full sm:w-auto">
            <label for="city-select" class="sr-only">Şehir seç</label>
            <select id="city-select" class="bg-surface-container-low border-none rounded-lg py-2 px-3 text-body-md font-body-md text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all max-w-xs truncate" title="Şehir seçiniz">
                <option value="">Tüm Şehirler</option>
            </select>
        </div>
        <div class="relative w-full sm:w-80">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input class="w-full bg-surface-container-low border-none rounded-lg py-3 pl-10 pr-4 text-body-md font-body-md text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all" id="map-search" placeholder="Mekan, kategori ara..." type="text"/>
        </div>
    </div>

    <!-- Chips -->
    <div class="flex gap-2 flex-wrap pb-2" id="map-filters">
        <button class="whitespace-nowrap px-4 py-1.5 rounded-full bg-primary text-on-primary font-label-md text-label-md transition-colors" data-category="">Tümü</button>
        <button class="whitespace-nowrap px-4 py-1.5 rounded-full bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest font-label-md text-label-md transition-colors" data-category="Tarihi Yer">Tarihi</button>
        <button class="whitespace-nowrap px-4 py-1.5 rounded-full bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest font-label-md text-label-md transition-colors" data-category="Doğa">Doğa</button>
        <button class="whitespace-nowrap px-4 py-1.5 rounded-full bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest font-label-md text-label-md transition-colors" data-category="Müze">Müze</button>
        <button class="whitespace-nowrap px-4 py-1.5 rounded-full bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest font-label-md text-label-md transition-colors" data-category="Park">Park</button>
    </div>
</div>
<!-- Locations List -->
<div class="flex-grow overflow-y-auto px-6 py-5 space-y-6" id="map-location-list"></div>
<!-- Route Builder Action Area -->
<div class="p-4 border-t border-surface-variant bg-surface-container-lowest shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
<div class="flex justify-between items-center mb-3">
<div class="flex items-center gap-4">
    <span class="font-label-md text-label-md text-on-surface">Mevcut Rota: <strong id="route-count" class="text-primary">0 Durak</strong></span>
    <button id="route-clear" class="text-sm text-on-surface-variant underline hidden">Temizle</button>
</div>
<span class="font-label-md text-label-md text-on-surface-variant text-xs">Tahmini Süre: 2 sa</span>
</div>
<button id="create-route-button" class="w-full bg-secondary text-on-secondary font-label-md text-label-md py-3 rounded-lg hover:bg-on-secondary-fixed transition-colors shadow-sm flex items-center justify-center gap-2 opacity-60 cursor-not-allowed" disabled>
    <span class="material-symbols-outlined">map</span> Rota Oluştur
</button>
<p id="route-hint" class="text-sm text-on-surface-variant mt-2 text-center">Rota oluşturmak için bir yer seçin.</p>
</div>
</aside>
<!-- Map Area -->
<section class="flex-grow relative bg-surface-variant" data-location="Istanbul">
<div id="map" class="absolute inset-0"></div>
</section>
</main>
<script>
    window.API_BASE = '../api';
    window.__CSRF_TOKEN__ = '<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES); ?>';
    window.__IS_LOGGED_IN__ = <?php echo ($isLoggedIn ? 'true' : 'false'); ?>;
    window.__CURRENT_USER_FIRST_NAME__ = '<?php echo htmlspecialchars($currentUserFirstName, ENT_QUOTES); ?>';
</script>
<script src="js/map.js" defer></script>
<!-- Route Create Modal -->
<div id="route-modal" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-[9999]">
    <div class="bg-surface rounded-lg p-6 w-full max-w-md mx-4 shadow-[0_12px_45px_rgba(0,0,0,0.30)]">
        <h3 class="font-headline-md text-headline-md mb-3">Rota Oluştur</h3>
        <form id="route-create-form" class="space-y-3">
            <div>
                <label class="text-sm text-on-surface-variant">Rota adı</label>
                <input id="route-create-name" class="w-full rounded-lg border border-outline-variant px-3 py-2 mt-1" placeholder="Rota adını girin" required />
            </div>
            <div>
                <label class="text-sm text-on-surface-variant">Gizlilik</label>
                <select id="route-create-public" class="w-full rounded-lg border border-outline-variant px-3 py-2 mt-1">
                    <option value="1">Herkese Açık</option>
                    <option value="0">Sadece Ben</option>
                </select>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" id="route-create-submit" class="flex-1 bg-primary text-on-primary py-2 rounded-lg">Oluştur</button>
                <button type="button" id="route-create-cancel" class="flex-1 border border-outline-variant py-2 rounded-lg">İptal</button>
            </div>
            <p id="route-create-error" class="text-sm text-error hidden"></p>
        </form>
    </div>
</div>
<?php include '../includes/close.php'; ?>