<?php header('Location: admin-manager.php');
exit; ?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Anatolia.go - Admin Paneli</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Be+Vietnam+Pro:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "outline": "#6d797b",
                        "on-primary-fixed-variant": "#004f56",
                        "background": "#f8f9fa",
                        "on-surface": "#191c1d",
                        "on-secondary-fixed-variant": "#77320e",
                        "on-secondary-fixed": "#360f00",
                        "on-tertiary-fixed": "#2c1700",
                        "surface": "#f8f9fa",
                        "tertiary": "#795833",
                        "on-background": "#191c1d",
                        "surface-container-highest": "#e1e3e4",
                        "inverse-on-surface": "#f0f1f2",
                        "on-secondary-container": "#76300d",
                        "tertiary-container": "#b48c63",
                        "primary": "#006972",
                        "secondary-fixed": "#ffdbcd",
                        "inverse-primary": "#5dd7e6",
                        "on-tertiary-fixed-variant": "#5f401d",
                        "tertiary-fixed-dim": "#ebbe91",
                        "surface-variant": "#e1e3e4",
                        "secondary-container": "#fd9a6f",
                        "surface-tint": "#006972",
                        "tertiary-fixed": "#ffdcbb",
                        "surface-bright": "#f8f9fa",
                        "on-tertiary": "#ffffff",
                        "outline-variant": "#bcc9cb",
                        "primary-fixed": "#8df2ff",
                        "on-secondary": "#ffffff",
                        "on-primary-container": "#003237",
                        "surface-container-lowest": "#ffffff",
                        "on-primary": "#ffffff",
                        "secondary-fixed-dim": "#ffb597",
                        "surface-dim": "#d9dadb",
                        "error-container": "#ffdad6",
                        "inverse-surface": "#2e3132",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#001f23",
                        "surface-container-low": "#f3f4f5",
                        "on-tertiary-container": "#412706",
                        "primary-container": "#00a3b1",
                        "secondary": "#964824",
                        "on-surface-variant": "#3d494b",
                        "primary-fixed-dim": "#5dd7e6",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "surface-container-high": "#e7e8e9",
                        "surface-container": "#edeeef"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "margin-mobile": "20px",
                        "unit": "4px",
                        "gutter": "16px",
                        "margin-desktop": "64px",
                        "card-gap": "24px"
                    },
                    "fontFamily": {
                        "headline-xl": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "body-md": ["Be Vietnam Pro"],
                        "body-lg": ["Be Vietnam Pro"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"]
                    },
                    "fontSize": {
                        "headline-xl": ["48px", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "1.4", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "1.6", "fontWeight": "400" }],
                        "body-lg": ["18px", { "lineHeight": "1.6", "fontWeight": "400" }],
                        "headline-lg": ["32px", { "lineHeight": "1.3", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "label-md": ["14px", { "lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "600" }]
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-background text-on-background font-body-md min-h-screen flex antialiased">
    <!-- JSON Component: SideNavBar -->
    <aside
        class="flex flex-col fixed left-0 top-0 h-full py-6 h-screen w-64 border-r border-r border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 z-50">
        <!-- Header -->
        <div class="px-6 mb-8 flex items-center gap-4">
            <img alt="Admin Profil" class="w-12 h-12 rounded-full object-cover border border-zinc-200"
                data-alt="aydınlık bir ofiste profesyonel görünümlü bir kişi portresi"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCd5mJMGNDEtkBhbrXB0Ay02vAqslfwVv75bnRKlL-5glVVS-nF9r1dZC0Q-kwXwQNqVQxNf9H18lKZ9c_u-glvn221FK2mY_Yh1bKyHnPMVYBnQEbRKWWBZCFVlyilWuZSUQXo4H4YDw9GHcPr9SKFx5nUb4yA2u7KMvfQorL_87mplALJL4dCQUL1i4cp8_rfayMy-mQqQ7mPdA_o0tYB9xsrXTgOL56_icrCyNplvw2YtMmgD8BrtceWNjz5R2u64DIwEfkj-dc" />
            <div>
                <h1 class="text-lg font-bold text-cyan-600 font-['Plus_Jakarta_Sans']">Admin Panel</h1>
                <p class="font-['Plus_Jakarta_Sans'] text-sm text-zinc-500">Yönetim Konsolu</p>
                <a class="mt-2 inline-flex text-xs font-semibold text-cyan-600 hover:underline" href="index.php">Siteye
                    Dön</a>
            </div>
        </div>
        <!-- Navigation Links -->
        <nav class="flex-1 flex flex-col gap-1 w-full">
            <!-- Active Tab: Dashboard -->
            <a class="flex items-center gap-4 px-6 py-3 bg-cyan-50 dark:bg-cyan-950/30 text-cyan-700 dark:text-cyan-300 font-semibold border-r-4 border-cyan-600 font-['Plus_Jakarta_Sans'] text-sm"
                href="#">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                Gösterge Paneli
            </a>
            <!-- Inactive Tabs -->
            <a class="flex items-center gap-4 px-6 py-3 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:translate-x-1 transition-transform duration-200 font-['Plus_Jakarta_Sans'] text-sm"
                href="#">
                <span class="material-symbols-outlined">location_on</span>
                Şehirler
            </a>
            <a class="flex items-center gap-4 px-6 py-3 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:translate-x-1 transition-transform duration-200 font-['Plus_Jakarta_Sans'] text-sm"
                href="#">
                <span class="material-symbols-outlined">map</span>
                Rotalar
            </a>
            <a class="flex items-center gap-4 px-6 py-3 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:translate-x-1 transition-transform duration-200 font-['Plus_Jakarta_Sans'] text-sm"
                href="#">
                <span class="material-symbols-outlined">group</span>
                Kullanıcılar
            </a>
            <a class="flex items-center gap-4 px-6 py-3 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:translate-x-1 transition-transform duration-200 font-['Plus_Jakarta_Sans'] text-sm"
                href="#">
                <span class="material-symbols-outlined">insights</span>
                Analitik
            </a>
        </nav>
    </aside>
    <!-- Main Content Area -->
    <main class="flex-1 ml-64 p-margin-desktop bg-surface min-h-screen">
        <!-- Page Header -->
        <header class="mb-12 flex justify-between items-end">
            <div>
                <p class="font-label-md text-label-md text-primary mb-2 uppercase tracking-widest">Genel Bakış</p>
                <h2 class="font-headline-xl text-headline-xl text-on-surface">Platform Paneli</h2>
            </div>
            <button
                class="bg-primary text-on-primary px-6 py-3 rounded-lg font-label-md text-label-md shadow-[0_4px_20px_rgba(0,105,114,0.06)] hover:shadow-[0_8px_30px_rgba(0,105,114,0.1)] hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">download</span>
                Raporu Dışa Aktar
            </button>
        </header>
        <!-- Stats Grid (Bento Style) -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-card-gap mb-16">
            <!-- Stat Card 1 -->
            <div
                class="bg-surface-container-lowest rounded-xl p-8 shadow-[0_4px_20px_rgba(0,105,114,0.06)] flex flex-col justify-between border border-surface-container-low">
                <div class="flex justify-between items-start mb-6">
                    <div class="bg-primary-container/20 p-3 rounded-lg text-primary">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <span
                        class="font-label-md text-label-md text-primary bg-primary/10 px-3 py-1 rounded-full flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">trending_up</span> +12%
                    </span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">
                        <?php echo number_format($statsUsers); ?></h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Toplam Aktif Kullanıcı</p>
                </div>
            </div>
            <!-- Stat Card 2 -->
            <div
                class="bg-surface-container-lowest rounded-xl p-8 shadow-[0_4px_20px_rgba(0,105,114,0.06)] flex flex-col justify-between border border-surface-container-low">
                <div class="flex justify-between items-start mb-6">
                    <div class="bg-tertiary-container/20 p-3 rounded-lg text-tertiary">
                        <span class="material-symbols-outlined">map</span>
                    </div>
                    <span
                        class="font-label-md text-label-md text-primary bg-primary/10 px-3 py-1 rounded-full flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">trending_up</span> +8%
                    </span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">
                        <?php echo number_format($statsRoutes); ?></h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Paylaşılan Rota</p>
                </div>
            </div>
            <!-- Stat Card 3 -->
            <div
                class="bg-surface-container-lowest rounded-xl p-8 shadow-[0_4px_20px_rgba(0,105,114,0.06)] flex flex-col justify-between border border-surface-container-low">
                <div class="flex justify-between items-start mb-6">
                    <div class="bg-secondary-container/20 p-3 rounded-lg text-secondary">
                        <span class="material-symbols-outlined">location_city</span>
                    </div>
                    <span
                        class="font-label-md text-label-md text-outline px-3 py-1 rounded-full flex items-center gap-1">
                        Stabil
                    </span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">
                        <?php echo htmlspecialchars($topCitiesText, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">En Popüler Şehirler</p>
                </div>
            </div>
        </section>
        <!-- Management Section -->
        <div class="space-y-12">
            <!-- Locations Table -->
            <section
                class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,105,114,0.06)] border border-surface-container-low overflow-hidden">
                <div class="px-8 py-6 border-b border-surface-container-low flex flex-col gap-4 bg-surface-bright">
                    <div class="flex justify-between items-center">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Konum Yönetimi</h3>
                    </div>
                    <form class="grid grid-cols-1 md:grid-cols-6 gap-3" id="location-create-form" data-mode="create">
                        <input type="hidden" id="loc-id" value="" />
                        <input
                            class="md:col-span-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="loc-name" placeholder="Konum adı" required type="text" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="loc-category" placeholder="Kategori" required type="text" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="loc-city-id" placeholder="City ID" required type="number" min="1" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="loc-lat" placeholder="Lat" type="number" step="0.00000001" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="loc-lng" placeholder="Lng" type="number" step="0.00000001" />
                        <button class="md:col-span-1 bg-primary text-on-primary rounded-lg px-4 py-2 font-label-md"
                            id="loc-submit" type="submit">Ekle</button>
                        <button class="md:col-span-1 border border-outline-variant rounded-lg px-4 py-2 font-label-md"
                            id="loc-reset" type="button">Sıfırla</button>
                        <p class="md:col-span-6 text-sm text-error" id="location-create-error" role="alert"></p>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="locations-table">
                        <thead>
                            <tr
                                class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <th class="px-8 py-4 font-semibold">Konum Adı</th>
                                <th class="px-8 py-4 font-semibold">Şehir</th>
                                <th class="px-8 py-4 font-semibold">Kategori</th>
                                <th class="px-8 py-4 font-semibold text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface">
                            <?php if (count($pendingLocations) === 0): ?>
                                <tr class="border-b border-surface-container">
                                    <td class="px-8 py-5 text-on-surface-variant" colspan="4">Kayıt bulunamadı.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingLocations as $location): ?>
                                    <tr class="border-b border-surface-container hover:bg-surface-container-low/50 transition-colors"
                                        data-location-id="<?php echo (int) $location['id']; ?>"
                                        data-location-name="<?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-location-category="<?php echo htmlspecialchars($location['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-location-city-id="<?php echo (int) $location['city_id']; ?>">
                                        <td class="px-8 py-5 flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded bg-tertiary-container/20 flex items-center justify-center text-tertiary">
                                                <span class="material-symbols-outlined text-[20px]">location_on</span></div>
                                            <span
                                                class="font-semibold"><?php echo htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td class="px-8 py-5 text-on-surface-variant">
                                            <?php echo htmlspecialchars($location['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="px-8 py-5"><span
                                                class="bg-surface-container px-3 py-1 rounded-full text-sm"><?php echo htmlspecialchars($location['category'] ?? 'Diğer', ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td class="px-8 py-5 text-right space-x-2">
                                            <button
                                                class="p-2 text-primary hover:bg-primary-container/20 transition-colors rounded-lg js-edit-location"
                                                title="Düzenle"><span
                                                    class="material-symbols-outlined text-[20px]">edit</span></button>
                                            <button
                                                class="p-2 text-outline hover:text-error transition-colors rounded-lg hover:bg-error-container/20 js-delete-location"
                                                title="Sil"><span
                                                    class="material-symbols-outlined text-[20px]">delete</span></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- Routes Management -->
            <section
                class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,105,114,0.06)] border border-surface-container-low overflow-hidden">
                <div class="px-8 py-6 border-b border-surface-container-low flex flex-col gap-4 bg-surface-bright">
                    <div class="flex justify-between items-center">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Rota Yönetimi</h3>
                    </div>
                    <form class="grid grid-cols-1 md:grid-cols-5 gap-3" id="route-create-form" data-mode="create">
                        <input type="hidden" id="route-id" value="" />
                        <input
                            class="md:col-span-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="route-name" placeholder="Rota adı" required type="text" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="route-user-id" placeholder="User ID" required type="number" min="1" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="route-city-id" placeholder="City ID" required type="number" min="1" />
                        <select class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="route-public">
                            <option value="1">Public</option>
                            <option value="0">Private</option>
                        </select>
                        <button class="md:col-span-1 bg-primary text-on-primary rounded-lg px-4 py-2 font-label-md"
                            id="route-submit" type="submit">Ekle</button>
                        <button class="md:col-span-1 border border-outline-variant rounded-lg px-4 py-2 font-label-md"
                            id="route-reset" type="button">Sıfırla</button>
                        <p class="md:col-span-5 text-sm text-error" id="route-create-error" role="alert"></p>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="routes-table">
                        <thead>
                            <tr
                                class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <th class="px-8 py-4 font-semibold">Rota</th>
                                <th class="px-8 py-4 font-semibold">Şehir</th>
                                <th class="px-8 py-4 font-semibold">Oluşturan</th>
                                <th class="px-8 py-4 font-semibold text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface">
                            <?php
                            try {
                                $routesAdminStmt = $pdo->query("SELECT r.route_id, r.route_name, r.is_public,
            r.city_id, r.user_id,
            c.name AS city_name,
            CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM routes r
        LEFT JOIN cities c ON r.city_id = c.id
        LEFT JOIN users u ON r.user_id = u.id
        ORDER BY r.route_id DESC
        LIMIT 10");
                                $routesAdmin = $routesAdminStmt->fetchAll();
                            } catch (Exception $e) {
                                $routesAdmin = [];
                            }
                            ?>
                            <?php if (count($routesAdmin) === 0): ?>
                                <tr class="border-b border-surface-container">
                                    <td class="px-8 py-5 text-on-surface-variant" colspan="4">Kayıt bulunamadı.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($routesAdmin as $route): ?>
                                    <tr class="border-b border-surface-container hover:bg-surface-container-low/50 transition-colors"
                                        data-route-id="<?php echo (int) $route['route_id']; ?>"
                                        data-route-name="<?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-route-user-id="<?php echo (int) $route['user_id']; ?>"
                                        data-route-city-id="<?php echo (int) $route['city_id']; ?>"
                                        data-route-public="<?php echo (int) $route['is_public']; ?>">
                                        <td class="px-8 py-5 font-semibold">
                                            <?php echo htmlspecialchars($route['route_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-8 py-5 text-on-surface-variant">
                                            <?php echo htmlspecialchars($route['city_name'] ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="px-8 py-5 text-on-surface-variant">
                                            <?php echo htmlspecialchars($route['author_name'] ?? 'Anonim', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <button
                                                class="p-2 text-primary hover:bg-primary-container/20 transition-colors rounded-lg js-edit-route"
                                                title="Düzenle"><span
                                                    class="material-symbols-outlined text-[20px]">edit</span></button>
                                            <button
                                                class="p-2 text-outline hover:text-error transition-colors rounded-lg hover:bg-error-container/20 js-delete-route"
                                                title="Sil"><span
                                                    class="material-symbols-outlined text-[20px]">delete</span></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Ratings Management -->
            <section
                class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,105,114,0.06)] border border-surface-container-low overflow-hidden">
                <div class="px-8 py-6 border-b border-surface-container-low flex flex-col gap-4 bg-surface-bright">
                    <div class="flex justify-between items-center">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Puan Yönetimi</h3>
                    </div>
                    <form class="grid grid-cols-1 md:grid-cols-4 gap-3" id="rating-create-form" data-mode="create">
                        <input type="hidden" id="rating-id" value="" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="rating-location-id" placeholder="Location ID" required type="number" min="1" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="rating-user-id" placeholder="User ID" required type="number" min="1" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="rating-star" placeholder="Puan (1-5)" required type="number" min="1" max="5" />
                        <button class="bg-primary text-on-primary rounded-lg px-4 py-2 font-label-md" id="rating-submit"
                            type="submit">Ekle</button>
                        <button class="border border-outline-variant rounded-lg px-4 py-2 font-label-md"
                            id="rating-reset" type="button">Sıfırla</button>
                        <p class="md:col-span-4 text-sm text-error" id="rating-create-error" role="alert"></p>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="ratings-table">
                        <thead>
                            <tr
                                class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <th class="px-8 py-4 font-semibold">ID</th>
                                <th class="px-8 py-4 font-semibold">Location</th>
                                <th class="px-8 py-4 font-semibold">User</th>
                                <th class="px-8 py-4 font-semibold">Puan</th>
                                <th class="px-8 py-4 font-semibold text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface">
                            <?php
                            try {
                                $ratingsAdminStmt = $pdo->query("SELECT rating_id, location_id, user_id, star_count
        FROM ratings ORDER BY rating_id DESC LIMIT 10");
                                $ratingsAdmin = $ratingsAdminStmt->fetchAll();
                            } catch (Exception $e) {
                                $ratingsAdmin = [];
                            }
                            ?>
                            <?php if (count($ratingsAdmin) === 0): ?>
                                <tr class="border-b border-surface-container">
                                    <td class="px-8 py-5 text-on-surface-variant" colspan="5">Kayıt bulunamadı.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ratingsAdmin as $rating): ?>
                                    <tr class="border-b border-surface-container hover:bg-surface-container-low/50 transition-colors"
                                        data-rating-id="<?php echo (int) $rating['rating_id']; ?>"
                                        data-rating-location="<?php echo (int) $rating['location_id']; ?>"
                                        data-rating-user="<?php echo (int) $rating['user_id']; ?>"
                                        data-rating-star="<?php echo (int) $rating['star_count']; ?>">
                                        <td class="px-8 py-5 font-semibold">#<?php echo (int) $rating['rating_id']; ?></td>
                                        <td class="px-8 py-5 text-on-surface-variant"><?php echo (int) $rating['location_id']; ?>
                                        </td>
                                        <td class="px-8 py-5 text-on-surface-variant"><?php echo (int) $rating['user_id']; ?>
                                        </td>
                                        <td class="px-8 py-5 text-on-surface-variant"><?php echo (int) $rating['star_count']; ?>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <button
                                                class="p-2 text-primary hover:bg-primary-container/20 transition-colors rounded-lg js-edit-rating"
                                                title="Düzenle"><span
                                                    class="material-symbols-outlined text-[20px]">edit</span></button>
                                            <button
                                                class="p-2 text-outline hover:text-error transition-colors rounded-lg hover:bg-error-container/20 js-delete-rating"
                                                title="Sil"><span
                                                    class="material-symbols-outlined text-[20px]">delete</span></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Cities Management -->
            <section
                class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,105,114,0.06)] border border-surface-container-low overflow-hidden">
                <div class="px-8 py-6 border-b border-surface-container-low flex flex-col gap-4 bg-surface-bright">
                    <div class="flex justify-between items-center">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Şehir Yönetimi</h3>
                    </div>
                    <form class="grid grid-cols-1 md:grid-cols-3 gap-3" id="city-create-form" data-mode="create">
                        <input type="hidden" id="city-id" value="" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="city-name" placeholder="Şehir adı" required type="text" />
                        <input class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2"
                            id="city-plate" placeholder="Plaka" required type="text" />
                        <button class="bg-primary text-on-primary rounded-lg px-4 py-2 font-label-md" id="city-submit"
                            type="submit">Ekle</button>
                        <button class="border border-outline-variant rounded-lg px-4 py-2 font-label-md" id="city-reset"
                            type="button">Sıfırla</button>
                        <p class="md:col-span-3 text-sm text-error" id="city-create-error" role="alert"></p>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="cities-table">
                        <thead>
                            <tr
                                class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">
                                <th class="px-8 py-4 font-semibold">Şehir</th>
                                <th class="px-8 py-4 font-semibold">Plaka</th>
                                <th class="px-8 py-4 font-semibold text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface">
                            <?php
                            try {
                                $citiesAdminStmt = $pdo->query("SELECT id, name, plate_code FROM cities ORDER BY id DESC LIMIT 10");
                                $citiesAdmin = $citiesAdminStmt->fetchAll();
                            } catch (Exception $e) {
                                $citiesAdmin = [];
                            }
                            ?>
                            <?php if (count($citiesAdmin) === 0): ?>
                                <tr class="border-b border-surface-container">
                                    <td class="px-8 py-5 text-on-surface-variant" colspan="3">Kayıt bulunamadı.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($citiesAdmin as $city): ?>
                                    <tr class="border-b border-surface-container hover:bg-surface-container-low/50 transition-colors"
                                        data-city-id="<?php echo (int) $city['id']; ?>"
                                        data-city-name="<?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-city-plate="<?php echo htmlspecialchars($city['plate_code'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <td class="px-8 py-5 font-semibold">
                                            <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-8 py-5 text-on-surface-variant">
                                            <?php echo htmlspecialchars($city['plate_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-8 py-5 text-right">
                                            <button
                                                class="p-2 text-primary hover:bg-primary-container/20 transition-colors rounded-lg js-edit-city"
                                                title="Düzenle"><span
                                                    class="material-symbols-outlined text-[20px]">edit</span></button>
                                            <button
                                                class="p-2 text-outline hover:text-error transition-colors rounded-lg hover:bg-error-container/20 js-delete-city"
                                                title="Sil"><span
                                                    class="material-symbols-outlined text-[20px]">delete</span></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- Grid for smaller tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-card-gap">
                <!-- Reported Content Table -->
                <section
                    class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,105,114,0.06)] border border-surface-container-low overflow-hidden">
                    <div class="px-6 py-5 border-b border-surface-container-low bg-surface-bright">
                        <h3 class="font-headline-md text-headline-md text-on-surface text-[20px]">Bildirilen İçerik</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-xs">
                                    <th class="px-6 py-3 font-semibold">Öğe</th>
                                    <th class="px-6 py-3 font-semibold">Neden</th>
                                    <th class="px-6 py-3 font-semibold text-right">İncele</th>
                                </tr>
                            </thead>
                            <tbody class="font-body-md text-body-md text-on-surface text-sm">
                                <tr class="border-b border-surface-container hover:bg-surface-container-low/50">
                                    <td class="px-6 py-4"><span class="font-semibold block">Rota: Boğaz
                                            Koşusu</span><span class="text-xs text-on-surface-variant">ID: #9928</span>
                                    </td>
                                    <td class="px-6 py-4"><span
                                            class="text-error bg-error-container/30 px-2 py-1 rounded text-xs">Hatalı
                                            Güzergâh</span></td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="text-primary hover:underline font-label-md">İncele</button>
                                    </td>
                                </tr>
                                <tr class="border-b border-surface-container hover:bg-surface-container-low/50">
                                    <td class="px-6 py-4"><span class="font-semibold block">Yorum</span><span
                                            class="text-xs text-on-surface-variant">@user404 tarafından</span></td>
                                    <td class="px-6 py-4"><span
                                            class="text-error bg-error-container/30 px-2 py-1 rounded text-xs">İstenmeyen</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="text-primary hover:underline font-label-md">İncele</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                <!-- User Roles Table -->
                <section
                    class="bg-surface-container-lowest rounded-xl shadow-[0_4px_20px_rgba(0,105,114,0.06)] border border-surface-container-low overflow-hidden">
                    <div
                        class="px-6 py-5 border-b border-surface-container-low bg-surface-bright flex justify-between items-center">
                        <h3 class="font-headline-md text-headline-md text-on-surface text-[20px]">Kullanıcı Rolleri</h3>
                        <button
                            class="text-primary hover:bg-primary-container/20 p-1 rounded-md transition-colors"><span
                                class="material-symbols-outlined text-[20px]">add</span></button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-xs">
                                    <th class="px-6 py-3 font-semibold">Kullanıcı</th>
                                    <th class="px-6 py-3 font-semibold">Rol</th>
                                    <th class="px-6 py-3 font-semibold text-right">Düzenle</th>
                                </tr>
                            </thead>
                            <tbody class="font-body-md text-body-md text-on-surface text-sm">
                                <?php
                                try {
                                    $usersAdminStmt = $pdo->query("SELECT id, first_name, last_name, email, role
        FROM users ORDER BY id DESC LIMIT 10");
                                    $usersAdmin = $usersAdminStmt->fetchAll();
                                } catch (Exception $e) {
                                    $usersAdmin = [];
                                }
                                ?>
                                <?php if (count($usersAdmin) === 0): ?>
                                    <tr class="border-b border-surface-container">
                                        <td class="px-6 py-4 text-on-surface-variant" colspan="3">Kayıt bulunamadı.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usersAdmin as $user): ?>
                                        <tr class="border-b border-surface-container hover:bg-surface-container-low/50"
                                            data-user-id="<?php echo (int) $user['id']; ?>"
                                            data-user-role="<?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <td class="px-6 py-4 flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant font-bold text-xs">
                                                    <?php echo htmlspecialchars(mb_substr($user['first_name'], 0, 1, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div>
                                                    <span
                                                        class="font-semibold block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span
                                                        class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <select
                                                    class="rounded border border-outline-variant bg-surface-container-lowest px-2 py-1 text-xs user-role-select">
                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Standart</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-2">
                                                <button
                                                    class="text-outline hover:text-primary transition-colors js-update-user-role"><span
                                                        class="material-symbols-outlined text-[20px]">save</span></button>
                                                <button
                                                    class="text-outline hover:text-error transition-colors js-delete-user"><span
                                                        class="material-symbols-outlined text-[20px]">delete</span></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>

</html>
<script>
    const locationForm = document.getElementById('location-create-form');
    const locationCreateError = document.getElementById('location-create-error');

    locationForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        locationCreateError.textContent = '';

        const payload = {
            id: document.getElementById('loc-id').value ? Number(document.getElementById('loc-id').value) : undefined,
            name: document.getElementById('loc-name').value.trim(),
            category: document.getElementById('loc-category').value.trim(),
            city_id: Number(document.getElementById('loc-city-id').value),
            lat: document.getElementById('loc-lat').value ? Number(document.getElementById('loc-lat').value) : null,
            lng: document.getElementById('loc-lng').value ? Number(document.getElementById('loc-lng').value) : null
        };

        const isEdit = locationForm.dataset.mode === 'edit';

        try {
            const response = await fetch('../api/admin/locations.php', {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || data.status === 'error') {
                locationCreateError.textContent = data.message || 'Kayıt oluşturulamadı.';
                return;
            }
            window.location.reload();
        } catch (error) {
            locationCreateError.textContent = 'Bağlantı hatası.';
        }
    });

    document.querySelectorAll('.js-edit-location').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            if (!row) {
                return;
            }
            document.getElementById('loc-id').value = row.dataset.locationId || '';
            document.getElementById('loc-name').value = row.dataset.locationName || '';
            document.getElementById('loc-category').value = row.dataset.locationCategory || '';
            document.getElementById('loc-city-id').value = row.dataset.locationCityId || '';
            document.getElementById('loc-submit').textContent = 'Güncelle';
            locationForm.dataset.mode = 'edit';
        });
    });

    document.querySelectorAll('.js-delete-location').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('tr');
            const locationId = Number(row?.dataset?.locationId);
            if (!locationId) {
                return;
            }

            if (!confirm('Bu konumu silmek istiyor musun?')) {
                return;
            }

            try {
                const response = await fetch('../api/admin/locations.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: locationId })
                });
                const data = await response.json();
                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Silme işlemi başarısız.');
                    return;
                }
                row.remove();
            } catch (error) {
                alert('Bağlantı hatası.');
            }
        });
    });

    const routeForm = document.getElementById('route-create-form');
    const routeCreateError = document.getElementById('route-create-error');

    routeForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        routeCreateError.textContent = '';

        const payload = {
            route_id: document.getElementById('route-id').value ? Number(document.getElementById('route-id').value) : undefined,
            route_name: document.getElementById('route-name').value.trim(),
            user_id: Number(document.getElementById('route-user-id').value),
            city_id: Number(document.getElementById('route-city-id').value),
            is_public: Number(document.getElementById('route-public').value)
        };

        const isEdit = routeForm.dataset.mode === 'edit';

        try {
            const response = await fetch('../api/admin/routes.php', {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || data.status === 'error') {
                routeCreateError.textContent = data.message || 'Kayıt oluşturulamadı.';
                return;
            }
            window.location.reload();
        } catch (error) {
            routeCreateError.textContent = 'Bağlantı hatası.';
        }
    });

    document.querySelectorAll('.js-edit-route').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            if (!row) {
                return;
            }
            document.getElementById('route-id').value = row.dataset.routeId || '';
            document.getElementById('route-name').value = row.dataset.routeName || '';
            document.getElementById('route-user-id').value = row.dataset.routeUserId || '';
            document.getElementById('route-city-id').value = row.dataset.routeCityId || '';
            document.getElementById('route-public').value = row.dataset.routePublic || '1';
            document.getElementById('route-submit').textContent = 'Güncelle';
            routeForm.dataset.mode = 'edit';
        });
    });

    document.querySelectorAll('.js-delete-route').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('tr');
            const routeId = Number(row?.dataset?.routeId);
            if (!routeId) {
                return;
            }

            if (!confirm('Bu rotayı silmek istiyor musun?')) {
                return;
            }

            try {
                const response = await fetch('../api/admin/routes.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ route_id: routeId })
                });
                const data = await response.json();
                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Silme işlemi başarısız.');
                    return;
                }
                row.remove();
            } catch (error) {
                alert('Bağlantı hatası.');
            }
        });
    });

    const ratingForm = document.getElementById('rating-create-form');
    const ratingCreateError = document.getElementById('rating-create-error');

    ratingForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        ratingCreateError.textContent = '';

        const payload = {
            rating_id: document.getElementById('rating-id').value ? Number(document.getElementById('rating-id').value) : undefined,
            location_id: Number(document.getElementById('rating-location-id').value),
            user_id: Number(document.getElementById('rating-user-id').value),
            star_count: Number(document.getElementById('rating-star').value)
        };

        const isEdit = ratingForm.dataset.mode === 'edit';

        try {
            const response = await fetch('../api/admin/ratings.php', {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || data.status === 'error') {
                ratingCreateError.textContent = data.message || 'Kayıt oluşturulamadı.';
                return;
            }
            window.location.reload();
        } catch (error) {
            ratingCreateError.textContent = 'Bağlantı hatası.';
        }
    });

    document.querySelectorAll('.js-edit-rating').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            if (!row) {
                return;
            }
            document.getElementById('rating-id').value = row.dataset.ratingId || '';
            document.getElementById('rating-location-id').value = row.dataset.ratingLocation || '';
            document.getElementById('rating-user-id').value = row.dataset.ratingUser || '';
            document.getElementById('rating-star').value = row.dataset.ratingStar || '';
            document.getElementById('rating-submit').textContent = 'Güncelle';
            ratingForm.dataset.mode = 'edit';
        });
    });

    document.querySelectorAll('.js-delete-rating').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('tr');
            const ratingId = Number(row?.dataset?.ratingId);
            if (!ratingId) {
                return;
            }

            if (!confirm('Bu puanı silmek istiyor musun?')) {
                return;
            }

            try {
                const response = await fetch('../api/admin/ratings.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rating_id: ratingId })
                });
                const data = await response.json();
                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Silme işlemi başarısız.');
                    return;
                }
                row.remove();
            } catch (error) {
                alert('Bağlantı hatası.');
            }
        });
    });

    const cityForm = document.getElementById('city-create-form');
    const cityCreateError = document.getElementById('city-create-error');

    cityForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        cityCreateError.textContent = '';

        const payload = {
            id: document.getElementById('city-id').value ? Number(document.getElementById('city-id').value) : undefined,
            name: document.getElementById('city-name').value.trim(),
            plate_code: document.getElementById('city-plate').value.trim()
        };

        const isEdit = cityForm.dataset.mode === 'edit';

        try {
            const response = await fetch('../api/admin/cities.php', {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || data.status === 'error') {
                cityCreateError.textContent = data.message || 'Kayıt oluşturulamadı.';
                return;
            }
            window.location.reload();
        } catch (error) {
            cityCreateError.textContent = 'Bağlantı hatası.';
        }
    });

    document.querySelectorAll('.js-edit-city').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            if (!row) {
                return;
            }
            document.getElementById('city-id').value = row.dataset.cityId || '';
            document.getElementById('city-name').value = row.dataset.cityName || '';
            document.getElementById('city-plate').value = row.dataset.cityPlate || '';
            document.getElementById('city-submit').textContent = 'Güncelle';
            cityForm.dataset.mode = 'edit';
        });
    });

    document.querySelectorAll('.js-delete-city').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('tr');
            const cityId = Number(row?.dataset?.cityId);
            if (!cityId) {
                return;
            }

            if (!confirm('Bu şehri silmek istiyor musun?')) {
                return;
            }

            try {
                const response = await fetch('../api/admin/cities.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: cityId })
                });
                const data = await response.json();
                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Silme işlemi başarısız.');
                    return;
                }
                row.remove();
            } catch (error) {
                alert('Bağlantı hatası.');
            }
        });
    });

    document.querySelectorAll('.js-update-user-role').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('tr');
            const userId = Number(row?.dataset?.userId);
            const select = row?.querySelector('.user-role-select');
            if (!userId || !select) {
                return;
            }

            try {
                const response = await fetch('../api/admin/users.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId, role: select.value })
                });
                const data = await response.json();
                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Rol güncellenemedi.');
                    return;
                }
            } catch (error) {
                alert('Bağlantı hatası.');
            }
        });
    });

    document.querySelectorAll('.js-delete-user').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('tr');
            const userId = Number(row?.dataset?.userId);
            if (!userId) {
                return;
            }

            if (!confirm('Bu kullanıcıyı silmek istiyor musun?')) {
                return;
            }

            try {
                const response = await fetch('../api/admin/users.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId })
                });
                const data = await response.json();
                if (!response.ok || data.status === 'error') {
                    alert(data.message || 'Silme işlemi başarısız.');
                    return;
                }
                row.remove();
            } catch (error) {
                alert('Bağlantı hatası.');
            }
        });
    });

    document.getElementById('loc-reset')?.addEventListener('click', () => {
        document.getElementById('loc-id').value = '';
        document.getElementById('loc-name').value = '';
        document.getElementById('loc-category').value = '';
        document.getElementById('loc-city-id').value = '';
        document.getElementById('loc-lat').value = '';
        document.getElementById('loc-lng').value = '';
        document.getElementById('loc-submit').textContent = 'Ekle';
        locationForm.dataset.mode = 'create';
    });

    document.getElementById('route-reset')?.addEventListener('click', () => {
        document.getElementById('route-id').value = '';
        document.getElementById('route-name').value = '';
        document.getElementById('route-user-id').value = '';
        document.getElementById('route-city-id').value = '';
        document.getElementById('route-public').value = '1';
        document.getElementById('route-submit').textContent = 'Ekle';
        routeForm.dataset.mode = 'create';
    });

    document.getElementById('rating-reset')?.addEventListener('click', () => {
        document.getElementById('rating-id').value = '';
        document.getElementById('rating-location-id').value = '';
        document.getElementById('rating-user-id').value = '';
        document.getElementById('rating-star').value = '';
        document.getElementById('rating-submit').textContent = 'Ekle';
        ratingForm.dataset.mode = 'create';
    });

    document.getElementById('city-reset')?.addEventListener('click', () => {
        document.getElementById('city-id').value = '';
        document.getElementById('city-name').value = '';
        document.getElementById('city-plate').value = '';
        document.getElementById('city-submit').textContent = 'Ekle';
        cityForm.dataset.mode = 'create';
    });
</script>