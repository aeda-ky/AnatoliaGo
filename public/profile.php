<?php
$pageTitle = 'Anatolia.go - Profil';
$activePage = 'profile';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background font-body-md text-body-md antialiased min-h-screen flex flex-col';
require_once '../includes/helpers/auth.php';
require_once '../includes/db_connect.php';

if (!require_auth()) {
	header('Location: auth.php');
	exit;
}

$userId = current_user_id();
$user = null;
$userRoutes = [];
$userRatings = [];
$favoriteRoutes = [];
$savedRoutes = [];
$favoritePlaces = [];
$routesCount = 0;
$visitedCount = 0;
$savedRoutesCount = 0;
$favoriteRoutesCount = 0;
$favoritePlacesCount = 0;

try {
	$userStmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
	$userStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$userStmt->execute();
	$user = $userStmt->fetch();

	$routesStmt = $pdo->prepare("SELECT route_id, route_name, creation_date
		FROM routes WHERE user_id = :id ORDER BY creation_date DESC LIMIT 12");
	$routesStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$routesStmt->execute();
	$userRoutes = $routesStmt->fetchAll();

	$routeCountStmt = $pdo->prepare("SELECT COUNT(*) FROM routes WHERE user_id = :id");
	$routeCountStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$routeCountStmt->execute();
	$routesCount = (int) $routeCountStmt->fetchColumn();

	$ratingsStmt = $pdo->prepare("SELECT r.rating_id, r.star_count, l.id AS location_id, l.name AS location_name, c.name AS city_name
		FROM ratings r
		INNER JOIN locations l ON r.location_id = l.id
		LEFT JOIN cities c ON l.city_id = c.id
		WHERE r.user_id = :id
		ORDER BY r.rating_date DESC
		LIMIT 12");
	$ratingsStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$ratingsStmt->execute();
	$userRatings = $ratingsStmt->fetchAll();

	$ratingCountStmt = $pdo->prepare("SELECT COUNT(*) FROM ratings WHERE user_id = :id");
	$ratingCountStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$ratingCountStmt->execute();
	$visitedCount = (int) $ratingCountStmt->fetchColumn();

	$favoritesStmt = $pdo->prepare("SELECT r.route_id, r.route_name, r.creation_date, c.name AS city_name,
		CONCAT(u.first_name, ' ', u.last_name) AS author_name, COUNT(rd.detail_id) AS stops
		FROM route_favorites f
		INNER JOIN routes r ON f.route_id = r.route_id
		LEFT JOIN cities c ON r.city_id = c.id
		LEFT JOIN users u ON r.user_id = u.id
		LEFT JOIN route_details rd ON rd.route_id = r.route_id
		WHERE f.user_id = :id
		GROUP BY r.route_id
		ORDER BY f.created_at DESC
		LIMIT 12");
	$favoritesStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$favoritesStmt->execute();
	$favoriteRoutes = $favoritesStmt->fetchAll();

	$favoriteRouteCountStmt = $pdo->prepare("SELECT COUNT(*) FROM route_favorites WHERE user_id = :id");
	$favoriteRouteCountStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$favoriteRouteCountStmt->execute();
	$favoriteRoutesCount = (int) $favoriteRouteCountStmt->fetchColumn();

	$savedStmt = $pdo->prepare("SELECT r.route_id, r.route_name, r.creation_date, s.created_at AS saved_date, c.name AS city_name,
		CONCAT(u.first_name, ' ', u.last_name) AS author_name, COUNT(rd.detail_id) AS stops
		FROM route_saved s
		INNER JOIN routes r ON s.route_id = r.route_id
		LEFT JOIN cities c ON r.city_id = c.id
		LEFT JOIN users u ON r.user_id = u.id
		LEFT JOIN route_details rd ON rd.route_id = r.route_id
		WHERE s.user_id = :id
		GROUP BY r.route_id
		ORDER BY s.created_at DESC
		LIMIT 12");
	$savedStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$savedStmt->execute();
	$savedRoutes = $savedStmt->fetchAll();

	$savedRouteCountStmt = $pdo->prepare("SELECT COUNT(*) FROM route_saved WHERE user_id = :id");
	$savedRouteCountStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$savedRouteCountStmt->execute();
	$savedRoutesCount = (int) $savedRouteCountStmt->fetchColumn();

	$favoritePlacesStmt = $pdo->prepare("SELECT l.id AS location_id, l.name, l.category, l.avg_rating, c.name AS city_name
		FROM location_favorites lf
		INNER JOIN locations l ON lf.location_id = l.id
		LEFT JOIN cities c ON l.city_id = c.id
		WHERE lf.user_id = :id
		ORDER BY lf.created_at DESC
		LIMIT 12");
	$favoritePlacesStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$favoritePlacesStmt->execute();
	$favoritePlaces = $favoritePlacesStmt->fetchAll();

	$favoritePlaceCountStmt = $pdo->prepare("SELECT COUNT(*) FROM location_favorites WHERE user_id = :id");
	$favoritePlaceCountStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$favoritePlaceCountStmt->execute();
	$favoritePlacesCount = (int) $favoritePlaceCountStmt->fetchColumn();

	// --- Statistics for analysis card ---
	$stats = [
		'avg_route_score' => null,
		'popular_route_name' => null,
		'total_interactions' => 0,
	];

	// Average route score: average of locations' avg_rating across user's routes
	$avgScoreStmt = $pdo->prepare("SELECT AVG(l.avg_rating) AS avg_score
        FROM routes r
        INNER JOIN route_details rd ON rd.route_id = r.route_id
        INNER JOIN locations l ON l.id = rd.location_id
        WHERE r.user_id = :id");
	$avgScoreStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$avgScoreStmt->execute();
	$row = $avgScoreStmt->fetch();
	if ($row && $row['avg_score'] !== null) {
		$stats['avg_route_score'] = round((float) $row['avg_score'], 2);
	}

	// Popular route: most saved or favorited
	$popularStmt = $pdo->prepare("SELECT r.route_id, r.route_name,
            COALESCE(rs.rs_count,0) + COALESCE(rf.rf_count,0) AS popularity
        FROM routes r
        LEFT JOIN (
            SELECT route_id, COUNT(*) AS rs_count FROM route_saved GROUP BY route_id
        ) rs ON rs.route_id = r.route_id
        LEFT JOIN (
            SELECT route_id, COUNT(*) AS rf_count FROM route_favorites GROUP BY route_id
        ) rf ON rf.route_id = r.route_id
        WHERE r.user_id = :id
        ORDER BY popularity DESC, r.creation_date DESC
        LIMIT 1");
	$popularStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	$popularStmt->execute();
	$pop = $popularStmt->fetch();
	if ($pop) {
		$stats['popular_route_name'] = $pop['route_name'];
	}

	// Total interactions across all user's routes: saves + favorites
	$totStmt = $pdo->prepare("SELECT 
            (SELECT COUNT(*) FROM route_saved WHERE route_id IN (SELECT route_id FROM routes WHERE user_id = :id)) + 
            (SELECT COUNT(*) FROM route_favorites WHERE route_id IN (SELECT route_id FROM routes WHERE user_id = :id)) 
        AS total");
	$totStmt->bindValue(':id', $userId, PDO::PARAM_INT);
	try {
		$totStmt->execute();
		$totRow = $totStmt->fetch();
		if ($totRow) {
			$stats['total_interactions'] = (int) $totRow['total'];
		}
	} catch (Exception $e) {
		// ignore
	}
} catch (Exception $e) {
	$user = null;
}
include '../includes/header.php';
?>
<!-- Main Content -->
<main class="flex-grow pt-[64px] pb-24 px-margin-mobile md:px-margin-desktop max-w-[1920px] mx-auto w-full">
	<!-- Profile Header -->
	<section class="grid grid-cols-1 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-6 mb-8 items-start">
		<div class="space-y-6">
			<div class="flex flex-col md:flex-row items-center md:items-start gap-6">
				<div
					class="w-28 h-28 md:w-40 md:h-40 rounded-full overflow-hidden flex-shrink-0 shadow-[0_8px_30px_rgba(0,0,0,0.06)] border-4 border-surface">
					<img id="profile-avatar" alt="User Avatar" class="w-full h-full object-cover" data-alt="user avatar"
						src="<?php echo htmlspecialchars($user['avatar'] ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuAlreWKPxpA5T1xqXw9p9upeEvWZPnVH2qb4G7ZunErWC3bOijJarjbDgrgFmKK5IhCSfFuMKnFa1q44IegOWABxkZ0LeNOQYR-eb2joE42yaOMqSdKvYARjBe2b1tW9X9EFJXrPCMeoBnPq9HXLWaLuxx45vsMomwY4TKM7kNbUWudoCbKLNpJpmcT5joLXnoGXwnd-8juMG98jjSlLtZFAHm0fz1Dt9bIdwdc1xY958qoDrB2SLVpGBG3WQy79sgP8ap7iZ5a56A'); ?>" />
				</div>
				<div class="flex flex-col items-center md:items-start text-center md:text-left">
					<?php
					function limit_words($text, $maxWords)
					{
						$text = trim((string) $text);
						if ($text === '') {
							return '';
						}
						$words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
						if (count($words) <= $maxWords) {
							return $text;
						}
						return implode(' ', array_slice($words, 0, $maxWords)) . '...';
					}
					$rawBio = $user['bio'] ?? '';
					$bioText = limit_words($rawBio, 20);
					?>
					<h1 id="profile-name" class="font-headline-xl text-headline-xl text-on-surface mb-1">
						<?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
					</h1>
					<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mb-3">
						<?php
						if ($bioText !== '') {
							echo htmlspecialchars($bioText, ENT_QUOTES, 'UTF-8');
						} else {
							echo 'Profil bilgileri ve paylaşımlarınız burada listelenir. Kaydedilen rotalar ve favoriler ayrı olarak listelenir.';
						}
						?>
					</p>
					<div class="flex flex-wrap md:flex-nowrap gap-8 items-center mb-6">
						<div class="flex-1 md:flex-none min-w-[120px] text-center">
							<span
								class="font-headline-md text-[28px] text-primary leading-none block"><?php echo (int) $routesCount; ?></span>
							<span
								class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Paylaşılan
								Rotalar</span>
						</div>
						<div class="flex-1 md:flex-none min-w-[120px] text-center">
							<span
								class="font-headline-md text-[28px] text-primary leading-none block"><?php echo (int) $visitedCount; ?></span>
							<span
								class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Ziyaret
								Edilen Yerler</span>
						</div>
						<div class="flex-1 md:flex-none min-w-[120px] text-center">
							<span
								class="font-headline-md text-[28px] text-primary leading-none block"><?php echo (int) $savedRoutesCount; ?></span>
							<span
								class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kaydedilen
								Rotalar</span>
						</div>
						<div class="flex-1 md:flex-none min-w-[120px] text-center">
							<span
								class="font-headline-md text-[28px] text-primary leading-none block"><?php echo (int) $favoriteRoutesCount; ?></span>
							<span
								class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Favori
								Rotalar</span>
						</div>
						<div class="flex-1 md:flex-none min-w-[120px] text-center">
							<span
								class="font-headline-md text-[28px] text-primary leading-none block"><?php echo (int) $favoritePlacesCount; ?></span>
							<span
								class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Favori
								Yerler</span>
						</div>
					</div>
					<div class="flex flex-wrap justify-center md:justify-start gap-3">
						<button id="open-profile-edit" type="button"
							class="inline-flex items-center justify-center bg-primary text-on-primary font-label-md text-label-md px-5 py-2 rounded-lg hover:bg-on-primary-fixed-variant transition-colors shadow-[0_4px_20px_rgba(0,105,114,0.12)] hover:shadow-[0_6px_20px_rgba(0,105,114,0.15)]">Profili
							Düzenle</button>
						<button id="share-profile-button"
							class="border border-outline text-on-surface font-label-md text-label-md px-5 py-2 rounded-lg hover:bg-surface-container transition-colors">Profili
							Paylaş</button>
					</div>
				</div>
			</div>
	</section>

	<!-- Tabbed Interface -->
	<section class="mb-12">
		<div class="flex border-b border-surface-variant mb-8 overflow-x-auto hide-scrollbar">
			<button
				class="profile-tab px-6 py-4 font-label-md text-label-md text-primary border-b-2 border-primary whitespace-nowrap"
				data-tab="saved">Kaydedilen Rotalarım</button>
			<button
				class="profile-tab px-6 py-4 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap"
				data-tab="favorites">Favori Rotalarım</button>
			<button
				class="profile-tab px-6 py-4 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap"
				data-tab="places">Favori Yerlerim</button>
			<button
				class="profile-tab px-6 py-4 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap"
				data-tab="ratings">Puanladığım Yerler</button>
			<button
				class="profile-tab px-6 py-4 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap"
				data-tab="my-routes">Benim Rotalarım</button>
		</div>
		<div id="tab-saved" class="profile-tab-content">
			<div class="columns-1 md:columns-2 lg:columns-3 gap-card-gap space-y-card-gap">
				<?php if (count($savedRoutes) === 0): ?>
					<div class="break-inside-avoid bg-surface rounded-xl p-6 text-on-surface-variant">Henüz rota
						kaydetmediniz.</div>
				<?php else: ?>
					<?php foreach ($savedRoutes as $route): ?>
						<div class="break-inside-avoid bg-surface rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(0,105,114,0.1)] transition-shadow duration-300 overflow-hidden flex flex-col group cursor-pointer"
							data-route-id="<?php echo (int) $route['route_id']; ?>">
							<div class="p-6 flex flex-col gap-2">
								<div class="flex items-center gap-2 text-tertiary">
									<span class="material-symbols-outlined text-[18px]"
										data-icon="location_on">location_on</span>
									<span class="font-label-md text-label-md uppercase">Kaydedilen</span>
								</div>
								<h3 class="font-headline-md text-headline-md text-on-surface">
									<a class="hover:underline"
										href="route-detail.php?id=<?php echo (int) $route['route_id']; ?>">
										<?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?>
									</a>
								</h3>
								<p class="font-body-md text-body-md text-on-surface-variant">Durak:
									<?php echo (int) $route['stops']; ?>
								</p>
								<p class="font-body-md text-body-md text-on-surface-variant">Kaydedildi:
									<?php echo htmlspecialchars(date('d.m.Y', strtotime($route['saved_date'] ?? $route['creation_date'])), ENT_QUOTES, 'UTF-8'); ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<div id="tab-favorites" class="profile-tab-content hidden">
			<div class="columns-1 md:columns-2 lg:columns-3 gap-card-gap space-y-card-gap">
				<?php if (count($favoriteRoutes) === 0): ?>
					<div class="break-inside-avoid bg-surface rounded-xl p-6 text-on-surface-variant">Henüz favori rota
						eklemediniz.</div>
				<?php else: ?>
					<?php foreach ($favoriteRoutes as $route): ?>
						<div class="break-inside-avoid bg-surface rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(0,105,114,0.1)] transition-shadow duration-300 overflow-hidden flex flex-col group cursor-pointer"
							data-route-id="<?php echo (int) $route['route_id']; ?>">
							<div class="p-6 flex flex-col gap-2">
								<div class="flex items-center gap-2 text-tertiary">
									<span class="material-symbols-outlined text-[18px]"
										data-icon="location_on">location_on</span>
									<span class="font-label-md text-label-md uppercase">Favori</span>
								</div>
								<h3 class="font-headline-md text-headline-md text-on-surface">
									<a class="hover:underline"
										href="route-detail.php?id=<?php echo (int) $route['route_id']; ?>">
										<?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?>
									</a>
								</h3>
								<p class="font-body-md text-body-md text-on-surface-variant">Durak:
									<?php echo (int) $route['stops']; ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<div id="tab-places" class="profile-tab-content hidden">
			<div class="columns-1 md:columns-2 lg:columns-3 gap-card-gap space-y-card-gap">
				<?php if (count($favoritePlaces) === 0): ?>
					<div class="break-inside-avoid bg-surface rounded-xl p-6 text-on-surface-variant">Henüz favori yer
						eklemediniz.</div>
				<?php else: ?>
					<?php foreach ($favoritePlaces as $place): ?>
						<div class="break-inside-avoid bg-surface rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(0,105,114,0.1)] transition-shadow duration-300 overflow-hidden flex flex-col group cursor-pointer"
							data-location-id="<?php echo (int) $place['location_id']; ?>">
							<div class="p-6 flex flex-col gap-2">
								<div class="flex items-center gap-2 text-tertiary">
									<span class="material-symbols-outlined text-[18px]"
										data-icon="location_on">location_on</span>
									<span class="font-label-md text-label-md uppercase">Favori</span>
								</div>
								<h3 class="font-headline-md text-headline-md text-on-surface">
									<a class="hover:underline"
										href="location-detail.php?id=<?php echo (int) $place['location_id']; ?>">
										<?php echo htmlspecialchars($place['name'], ENT_QUOTES, 'UTF-8'); ?>
									</a>
								</h3>
								<p class="font-body-md text-body-md text-on-surface-variant">
									<?php echo htmlspecialchars($place['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?>
									•
									<?php echo htmlspecialchars($place['category'] ?? 'Diğer', ENT_QUOTES, 'UTF-8'); ?>
								</p>
								<p class="font-body-md text-body-md text-on-surface-variant">Puan:
									<?php echo htmlspecialchars($place['avg_rating'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<div id="tab-my-routes" class="profile-tab-content hidden">
			<div class="columns-1 md:columns-2 lg:columns-3 gap-card-gap space-y-card-gap">
				<?php if (count($userRoutes) === 0): ?>
					<div class="break-inside-avoid bg-surface rounded-xl p-6 text-on-surface-variant">Henüz rota
						paylaşılmadı.</div>
				<?php else: ?>
					<?php foreach ($userRoutes as $route): ?>
						<div class="break-inside-avoid bg-surface rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(0,105,114,0.1)] transition-shadow duration-300 overflow-hidden flex flex-col group"
							data-route-id="<?php echo (int) $route['route_id']; ?>">
							<div class="p-6 flex flex-col gap-2 relative">
								<!-- Delete button -->
								<button type="button"
									class="route-delete-btn absolute top-4 right-4 bg-error/10 hover:bg-error/20 text-error p-2 rounded-full"
									data-route-id="<?php echo (int) $route['route_id']; ?>" aria-label="Rotayı Sil">
									<span class="material-symbols-outlined">delete</span>
								</button>
								<div class="flex items-center gap-2 text-tertiary">
									<span class="material-symbols-outlined text-[18px]"
										data-icon="location_on">location_on</span>
									<span class="font-label-md text-label-md uppercase">Rota</span>
								</div>
								<h3 class="font-headline-md text-headline-md text-on-surface">
									<a class="hover:underline"
										href="route-detail.php?id=<?php echo (int) $route['route_id']; ?>">
										<?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?>
									</a>
								</h3>
								<p class="font-body-md text-body-md text-on-surface-variant">Paylaşım:
									<?php echo htmlspecialchars(date('d.m.Y', strtotime($route['creation_date'])), ENT_QUOTES, 'UTF-8'); ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<div id="tab-ratings" class="profile-tab-content hidden">
			<div class="columns-1 md:columns-2 lg:columns-3 gap-card-gap space-y-card-gap">
				<?php if (count($userRatings) === 0): ?>
					<div class="break-inside-avoid bg-surface rounded-xl p-6 text-on-surface-variant">Henüz puanlama
						yok.</div>
				<?php else: ?>
					<?php foreach ($userRatings as $rating): ?>
						<div class="break-inside-avoid bg-surface rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_30px_rgba(0,105,114,0.1)] transition-shadow duration-300 overflow-hidden flex flex-col group cursor-pointer"
							data-location-id="<?php echo (int) $rating['location_id']; ?>">
							<div class="p-6 flex flex-col gap-2">
								<div class="flex items-center gap-2 text-tertiary">
									<span class="material-symbols-outlined text-[18px]"
										data-icon="location_on">location_on</span>
									<span
										class="font-label-md text-label-md uppercase"><?php echo htmlspecialchars($rating['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?></span>
								</div>
								<h3 class="font-headline-md text-headline-md text-on-surface">
									<a class="hover:underline"
										href="location-detail.php?id=<?php echo (int) $rating['location_id']; ?>">
										<?php echo htmlspecialchars($rating['location_name'], ENT_QUOTES, 'UTF-8'); ?>
									</a>
								</h3>
								<p class="font-body-md text-body-md text-on-surface-variant">Puan:
									<?php echo (int) $rating['star_count']; ?>/5
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<script>
	document.addEventListener('DOMContentLoaded', () => {
		const tabs = document.querySelectorAll('.profile-tab');
		const contents = document.querySelectorAll('.profile-tab-content');

		const activateTab = (tabName) => {
			contents.forEach((content) => {
				content.classList.toggle('hidden', content.id !== `tab-${tabName}`);
			});
			tabs.forEach((tab) => {
				const isActive = tab.dataset.tab === tabName;
				tab.classList.toggle('text-primary', isActive);
				tab.classList.toggle('border-b-2', isActive);
				tab.classList.toggle('border-primary', isActive);
				tab.classList.toggle('text-on-surface-variant', !isActive);
				tab.classList.toggle('hover:text-on-surface', !isActive);
			});
		};

		tabs.forEach((tab) => {
			tab.addEventListener('click', (event) => {
				event.preventDefault();
				activateTab(tab.dataset.tab);
			});
		});

		const shareButton = document.getElementById('share-profile-button');
		if (shareButton) {
			shareButton.addEventListener('click', async () => {
				const profileUrl = window.location.origin + window.location.pathname;
				if (navigator.share) {
					try {
						await navigator.share({
							title: document.title,
							text: 'Profilimi görüntüle',
							url: profileUrl,
						});
						return;
					} catch (shareError) {
						console.warn('Paylaşım iptal edildi veya desteklenmiyor.', shareError);
					}
				}
				try {
					navigator.clipboard.writeText(profileUrl);
					alert('Profil bağlantısı panoya kopyalandı.');
				} catch (copyError) {
					const promptMessage = 'Profil bağlantısını kopyalayın:';
					window.prompt(promptMessage, profileUrl);
				}
			});
		}

		// Default active tab
		activateTab('saved');
	});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	// CSRF token for AJAX calls
	window.__CSRF_TOKEN__ = '<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES); ?>';

	document.addEventListener('click', async (e) => {
		const btn = e.target.closest && e.target.closest('.route-delete-btn');
		if (!btn) return;
		e.preventDefault();
		const routeId = btn.dataset.routeId;
		if (!routeId) return;

		const result = await Swal.fire({
			title: 'Rota silinsin mi?',
			text: 'Bu işlem geri alınamaz.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Evet, sil',
			cancelButtonText: 'İptal',
			reverseButtons: true,
		});

		if (!result.isConfirmed) return;

		btn.disabled = true;
		try {
			const fd = new FormData();
			fd.append('csrf_token', window.__CSRF_TOKEN__ || '');
			fd.append('route_id', routeId);

			const res = await fetch('../api/routes/delete.php', { method: 'POST', body: fd });
			const data = await res.json();
			if (data && data.status === 'success') {
				const card = document.querySelector(`[data-route-id="${routeId}"]`);
				if (card) card.remove();
				Swal.fire({ icon: 'success', title: 'Silindi', text: 'Rota başarıyla silindi', timer: 1600, showConfirmButton: false });
			} else {
				const msg = (data && data.message) ? data.message : 'Silme işlemi başarısız.';
				Swal.fire({ icon: 'error', title: 'Hata', text: msg });
				btn.disabled = false;
			}
		} catch (err) {
			console.error(err);
			Swal.fire({ icon: 'error', title: 'Sunucu Hatası', text: 'Sunucu ile iletişim kurulamadı.' });
			btn.disabled = false;
		}
	});

	// Open profile edit page
	document.getElementById('open-profile-edit')?.addEventListener('click', (e) => {
		e.preventDefault();
		window.location.href = 'profile-edit.php';
	});
</script>
<!-- Profile Edit Modal (no preview) -->
<div id="profile-edit-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-[10000]">
	<div class="bg-surface rounded-lg p-6 w-full max-w-lg mx-4 shadow-[0_12px_45px_rgba(0,0,0,0.30)]">
		<h3 class="font-headline-md text-headline-md mb-3">Profili Düzenle</h3>
		<form id="profile-edit-form" class="space-y-3">
			<div>
				<label class="text-sm text-on-surface-variant">Ad</label>
				<input name="first_name" id="edit-first-name"
					class="w-full rounded-lg border border-outline-variant px-3 py-2 mt-1"
					value="<?php echo htmlspecialchars($user['first_name'] ?? '', ENT_QUOTES); ?>" />
			</div>
			<div>
				<label class="text-sm text-on-surface-variant">Soyad</label>
				<input name="last_name" id="edit-last-name"
					class="w-full rounded-lg border border-outline-variant px-3 py-2 mt-1"
					value="<?php echo htmlspecialchars($user['last_name'] ?? '', ENT_QUOTES); ?>" />
			</div>
			<div>
				<label class="text-sm text-on-surface-variant">Biyografi</label>
				<textarea name="bio" id="edit-bio"
					class="w-full rounded-lg border border-outline-variant px-3 py-2 mt-1" rows="4"
					data-max-words="20"><?php echo htmlspecialchars($user['bio'] ?? '', ENT_QUOTES); ?></textarea>
				<p id="bio-helper" class="text-xs text-on-surface-variant mt-1">Maksimum 20 kelime</p>
			</div>
			<div class="flex gap-2 pt-2">
				<button type="submit" id="profile-edit-submit"
					class="flex-1 bg-primary text-on-primary py-2 rounded-lg">Kaydet</button>
				<button type="button" id="profile-edit-cancel"
					class="flex-1 border border-outline-variant py-2 rounded-lg">İptal</button>
			</div>
		</form>
	</div>
</div>
<script>
	// Open modal instead of redirect
	document.getElementById('open-profile-edit')?.addEventListener('click', (e) => {
		e.preventDefault();
		document.getElementById('profile-edit-modal').classList.remove('hidden');
		document.getElementById('profile-edit-modal').classList.add('flex');
	});

	document.getElementById('profile-edit-cancel')?.addEventListener('click', (e) => {
		e.preventDefault();
		document.getElementById('profile-edit-modal').classList.add('hidden');
		document.getElementById('profile-edit-modal').classList.remove('flex');
	});

	document.getElementById('profile-edit-form')?.addEventListener('submit', async (e) => {
		e.preventDefault();
		const submitBtn = document.getElementById('profile-edit-submit');
		submitBtn.disabled = true;

		const fd = new FormData();
		fd.append('csrf_token', window.__CSRF_TOKEN__ || '');
		fd.append('first_name', document.getElementById('edit-first-name').value || '');
		fd.append('last_name', document.getElementById('edit-last-name').value || '');
		fd.append('bio', document.getElementById('edit-bio').value || '');

		try {
			const res = await fetch('../api/users/update_profile.php', { method: 'POST', body: fd });
			const data = await res.json();
			if (data && data.status === 'success') {
				const nameEl = document.getElementById('profile-name');
				const first = document.getElementById('edit-first-name').value || '';
				const last = document.getElementById('edit-last-name').value || '';
				if (nameEl) nameEl.textContent = (first + ' ' + last).trim();

				document.getElementById('profile-edit-modal').classList.add('hidden');
				document.getElementById('profile-edit-modal').classList.remove('flex');
				Swal.fire({ icon: 'success', title: 'Güncellendi', timer: 1200, showConfirmButton: false });
			} else {
				const msg = (data && data.message) ? data.message : 'Profil güncellenemedi.';
				Swal.fire({ icon: 'error', title: 'Hata', text: msg });
			}
		} catch (err) {
			console.error(err);
			Swal.fire({ icon: 'error', title: 'Sunucu Hatası', text: 'Sunucu ile iletişim kurulamadı.' });
		}
		submitBtn.disabled = false;
	});

	const bioInput = document.getElementById('edit-bio');
	const bioHelper = document.getElementById('bio-helper');
	if (bioInput) {
		const maxWords = parseInt(bioInput.dataset.maxWords || '20', 10);
		const updateBioCount = () => {
			const words = bioInput.value.trim().split(/\s+/).filter(Boolean);
			if (words.length > maxWords) {
				bioInput.value = words.slice(0, maxWords).join(' ');
			}
			const count = Math.min(words.length, maxWords);
			if (bioHelper) {
				bioHelper.textContent = `Maksimum ${maxWords} kelime (${count}/${maxWords})`;
			}
		};
		updateBioCount();
		bioInput.addEventListener('input', updateBioCount);
	}
</script>
<?php include '../includes/footer.php'; ?>