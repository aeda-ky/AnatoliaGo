<?php
$pageTitle = $pageTitle ?? 'Anatolia.go';
$lang = $lang ?? 'tr';
$activePage = $activePage ?? '';
$bodyClass = $bodyClass ?? 'bg-background text-on-background antialiased min-h-screen';
$extraHead = $extraHead ?? '';
$showAuthCta = $showAuthCta ?? false;
$hideNavIcons = $hideNavIcons ?? false;
require_once __DIR__ . '/helpers/auth.php';
$isLoggedIn = current_user_id() !== null;
$isAdmin = current_user_role() === 'admin';
?>
<!DOCTYPE html>
<html class="light" lang="<?php echo htmlspecialchars($lang, ENT_QUOTES); ?>">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Be+Vietnam+Pro:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
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
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "margin-mobile": "20px",
                        "unit": "4px",
                        "gutter": "16px",
                        "margin-desktop": "64px",
                        "card-gap": "24px"
                    },
                    fontFamily: {
                        "headline-xl": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "body-md": ["Be Vietnam Pro"],
                        "body-lg": ["Be Vietnam Pro"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
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
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined[data-weight="fill"] {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    <?php echo $extraHead; ?>
</head>
<body class="<?php echo htmlspecialchars($bodyClass, ENT_QUOTES); ?>">
<?php
function navItemClass($key, $activePage) {
    $base = "text-zinc-500 dark:text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors font-['Plus_Jakarta_Sans'] text-sm font-medium";
    if ($key === $activePage) {
        return "text-cyan-600 dark:text-cyan-400 border-b-2 border-cyan-600 dark:border-cyan-400 pb-1 font-['Plus_Jakarta_Sans'] text-sm font-medium";
    }
    return $base;
}
?>
<nav class="fixed top-0 w-full z-50 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-100 dark:border-zinc-800 shadow-[0_4px_20px_rgba(0,163,177,0.06)]">
    <div class="flex justify-between items-center h-16 px-4 sm:px-6 md:px-8 max-w-[1920px] mx-auto">
        <a class="text-2xl font-extrabold tracking-tight text-cyan-600 dark:text-cyan-400" href="index.php">Anatolia.go</a>
        <div class="hidden md:flex gap-8 items-center">
            <a class="<?php echo navItemClass('explore', $activePage); ?>" href="explore.php">Keşfet</a>
            <a class="<?php echo navItemClass('routes', $activePage); ?>" href="routes.php">Rotalar</a>
            <a class="<?php echo navItemClass('map', $activePage); ?>" href="map.php">Harita</a>
            <a class="<?php echo navItemClass('profile', $activePage); ?>" href="profile.php">Profil</a>
        </div>
        <?php if ($showAuthCta && !$isLoggedIn): ?>
            <a class="hidden md:inline-flex bg-primary text-on-primary px-4 py-2 rounded-lg text-sm font-semibold" href="auth.php">Giriş / Kayıt</a>
        <?php elseif (!$hideNavIcons): ?>
            <div class="hidden md:flex items-center gap-4 text-cyan-600 dark:text-cyan-400">
                <?php if ($isAdmin): ?>
                    <a class="text-sm font-semibold text-cyan-600 hover:underline" href="admin.php">Admin</a>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <a class="hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-lg transition-all px-3 py-2 flex items-center justify-center text-sm font-semibold" href="profile.php">Profil</a>
                    <a class="hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-lg transition-all px-3 py-2 flex items-center justify-center text-sm font-semibold" href="logout.php">Çıkış</a>
                <?php else: ?>
                    <a class="hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-lg transition-all px-3 py-2 flex items-center justify-center text-sm font-semibold" href="auth.php">Giriş</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <button id="mobile-nav-toggle" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-200" aria-expanded="false" aria-controls="mobile-nav" type="button">
            <span class="sr-only">Menüyü aç/kapat</span>
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    <div id="mobile-nav" class="md:hidden hidden border-t border-zinc-100 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95">
        <div class="px-4 sm:px-6 py-4 flex flex-col gap-3">
            <a class="<?php echo navItemClass('explore', $activePage); ?>" href="explore.php">Keşfet</a>
            <a class="<?php echo navItemClass('routes', $activePage); ?>" href="routes.php">Rotalar</a>
            <a class="<?php echo navItemClass('map', $activePage); ?>" href="map.php">Harita</a>
            <a class="<?php echo navItemClass('profile', $activePage); ?>" href="profile.php">Profil</a>
            <?php if ($showAuthCta && !$isLoggedIn): ?>
                <a class="mt-2 inline-flex bg-primary text-on-primary px-4 py-2 rounded-lg text-sm font-semibold w-fit" href="auth.php">Giriş / Kayıt</a>
            <?php elseif (!$hideNavIcons): ?>
                <div class="flex flex-col gap-2 text-cyan-600 dark:text-cyan-400">
                    <?php if ($isAdmin): ?>
                        <a class="text-sm font-semibold text-cyan-600 hover:underline" href="admin.php">Admin</a>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): ?>
                        <a class="hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-lg transition-all px-3 py-2 flex items-center justify-center text-sm font-semibold w-fit" href="profile.php">Profil</a>
                        <a class="hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-lg transition-all px-3 py-2 flex items-center justify-center text-sm font-semibold w-fit" href="logout.php">Çıkış</a>
                    <?php else: ?>
                        <a class="hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-lg transition-all px-3 py-2 flex items-center justify-center text-sm font-semibold w-fit" href="auth.php">Giriş</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
<script>
    const mobileNavToggle = document.getElementById('mobile-nav-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    if (mobileNavToggle && mobileNav) {
        mobileNavToggle.addEventListener('click', () => {
            const isOpen = !mobileNav.classList.contains('hidden');
            mobileNav.classList.toggle('hidden');
            mobileNavToggle.setAttribute('aria-expanded', String(!isOpen));
        });
    }
</script>
