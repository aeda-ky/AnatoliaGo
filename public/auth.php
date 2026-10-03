<?php
$pageTitle = 'Anatolia.go - Giriş & Kayıt';
$activePage = '';
$lang = 'tr';
$bodyClass = 'bg-background text-on-background min-h-screen antialiased';
$showAuthCta = true;
$hideNavIcons = true;
include '../includes/header.php';
require_once '../includes/helpers/auth.php';

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<main class="min-h-[calc(100vh-64px)] flex pt-16">
    <section class="hidden lg:flex w-1/2 relative">
        <img alt="Kapadokya" class="absolute inset-0 w-full h-full object-cover"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuBJ9nNzIMji99RXwpeb9duMQTfRIjFmF8mcAVkEcBVjXEn0bF1bhTRISwvpMuqmRLE349cGQ4AXSHY9G5fdpKIGChXRySIguHcpeESo3HNO5QadU42TMsryG_xa9nSnPH43_5pDG-IwoNXjSE8WARf6_psIZhh5gelF2LEavF-DWHRTjVJE4q8yPRrSKVII7VDsZoflz9dLyL4ykPoSzpYSPBgitLLzCuAGz3ElEFS6dNXp5yF2r4PwtppNG1sJpu9EiRYxgjXKl_M" />
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative z-10 p-12 text-white flex flex-col justify-end">
            <h1 class="font-headline-xl text-headline-xl mb-4">Anatolia.go</h1>
            <p class="font-body-lg text-body-lg max-w-md">Türkiye'nin kültürel mirasını ve rotalarını keşfetmeye hemen
                başla.</p>
        </div>
    </section>
    <section class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12">
        <div
            class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-[0_8px_30px_rgba(0,105,114,0.08)] p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-headline-md text-headline-md text-on-surface">Giriş Yap</h2>
                <span class="text-on-surface-variant text-sm">Yeni misin? <a class="text-primary font-semibold"
                        href="#register">Kayıt Ol</a></span>
            </div>
            <form class="space-y-4" id="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">
                <div>
                    <label class="block text-sm text-on-surface-variant mb-1">E-posta</label>
                    <input
                        class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 focus:ring-2 focus:ring-primary"
                        id="login-email" placeholder="ornek@mail.com" type="email" required />
                </div>
                <div>
                    <label class="block text-sm text-on-surface-variant mb-1">Şifre</label>
                    <input
                        class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 focus:ring-2 focus:ring-primary"
                        id="login-password" placeholder="••••••••" type="password" required />
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2">
                        <input class="rounded border-outline-variant" type="checkbox" />
                        Beni hatırla
                    </label>
                    <a class="text-primary" href="#">Şifremi unuttum</a>
                </div>
                <button class="w-full bg-primary text-on-primary py-3 rounded-lg font-semibold" type="submit">Giriş
                    Yap</button>
                <p class="text-sm text-error" id="login-error" role="alert"></p>
            </form>

            <hr class="my-8 border-outline-variant/40" />

            <div id="register" class="space-y-4">
                <h3 class="font-headline-md text-headline-md text-on-surface">Kayıt Ol</h3>
                <form class="space-y-4" id="register-form">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-on-surface-variant mb-1">Ad</label>
                            <input
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 focus:ring-2 focus:ring-primary"
                                id="register-first-name" placeholder="Ayşe" type="text" required />
                        </div>
                        <div>
                            <label class="block text-sm text-on-surface-variant mb-1">Soyad</label>
                            <input
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 focus:ring-2 focus:ring-primary"
                                id="register-last-name" placeholder="Yılmaz" type="text" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1">E-posta</label>
                        <input
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 focus:ring-2 focus:ring-primary"
                            id="register-email" placeholder="ornek@mail.com" type="email" required />
                    </div>
                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1">Şifre</label>
                        <input
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 focus:ring-2 focus:ring-primary"
                            id="register-password" placeholder="En az 8 karakter" type="password" minlength="8"
                            required />
                    </div>
                    <button class="w-full bg-secondary text-on-secondary py-3 rounded-lg font-semibold"
                        type="submit">Hesap Oluştur</button>
                    <p class="text-sm text-error" id="register-error" role="alert"></p>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    const registerForm = document.getElementById('register-form');
    const registerError = document.getElementById('register-error');

    async function handleAuthSubmit(form, url, payloadBuilder, errorEl) {
        errorEl.textContent = '';
        try {
            const payload = payloadBuilder();
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || data.status === 'error') {
                errorEl.textContent = data.message || 'Bir hata oluştu.';
                return;
            }
            window.location.href = 'index.php';
        } catch (error) {
            errorEl.textContent = 'Bağlantı hatası. Lütfen tekrar dene.';
        }
    }

    loginForm.addEventListener('submit', (event) => {
        event.preventDefault();
        handleAuthSubmit(loginForm, '../api/auth/login.php', () => ({
            csrf_token: loginForm.querySelector('input[name="csrf_token"]').value,
            email: document.getElementById('login-email').value.trim(),
            password: document.getElementById('login-password').value
        }), loginError);
    });

    registerForm.addEventListener('submit', (event) => {
        event.preventDefault();
        handleAuthSubmit(registerForm, '../api/auth/register.php', () => ({
            csrf_token: registerForm.querySelector('input[name="csrf_token"]').value,
            first_name: document.getElementById('register-first-name').value.trim(),
            last_name: document.getElementById('register-last-name').value.trim(),
            email: document.getElementById('register-email').value.trim(),
            password: document.getElementById('register-password').value
        }), registerError);
    });
</script>

<?php include '../includes/footer.php'; ?>