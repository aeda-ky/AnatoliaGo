<?php
$pageTitle = 'Anatolia.go - Profil Düzenle';
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
$message = '';
$errors = [];

try {
    // Ensure bio column exists (best-effort)
    try {
        $pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL');
    } catch (Exception $inner) {
        // ignore if DB does not support IF NOT EXISTS
    }

    $userStmt = $pdo->prepare('SELECT id, first_name, last_name, bio FROM users WHERE id = :id');
    $userStmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $userStmt->execute();
    $user = $userStmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        if (mb_strlen($bio, 'UTF-8') > 140) {
            $bio = mb_substr($bio, 0, 140, 'UTF-8');
        }

        if ($firstName === '') {
            $errors[] = 'Ad alanı boş olamaz.';
        }
        if ($lastName === '') {
            $errors[] = 'Soyad alanı boş olamaz.';
        }

        if (empty($errors)) {
            $updateStmt = $pdo->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, bio = :bio WHERE id = :id');
            $updateStmt->bindValue(':first_name', $firstName, PDO::PARAM_STR);
            $updateStmt->bindValue(':last_name', $lastName, PDO::PARAM_STR);
            $updateStmt->bindValue(':bio', $bio !== '' ? $bio : null, PDO::PARAM_STR);
            $updateStmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $updateStmt->execute();

            $message = 'Profil bilgileriniz güncellendi.';
            $user['first_name'] = $firstName;
            $user['last_name'] = $lastName;
            $user['bio'] = $bio;
        }
    }
} catch (Exception $e) {
    $errors[] = 'Profil bilgileri alınırken bir hata oluştu.';
}

include '../includes/header.php';
?>
<main class="flex-grow pt-[88px] pb-24 px-margin-mobile md:px-margin-desktop max-w-[1920px] mx-auto w-full">
    <section class="max-w-3xl mx-auto bg-surface rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] p-8">
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Profili Düzenle</h1>
        <?php if ($message): ?>
            <div class="mb-6 rounded-lg bg-secondary-container/20 border border-secondary-container p-4 text-secondary">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="mb-6 rounded-lg bg-error-container/15 border border-error p-4 text-error">
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form id="profile-edit-page-form" method="post" class="space-y-5">
            <div>
                <label class="block text-sm text-on-surface-variant mb-2" for="first_name">Ad</label>
                <input id="first_name" name="first_name" type="text"
                    value="<?php echo htmlspecialchars($user['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full rounded-lg border border-outline px-4 py-3 bg-surface" required />
            </div>
            <div>
                <label class="block text-sm text-on-surface-variant mb-2" for="last_name">Soyad</label>
                <input id="last_name" name="last_name" type="text"
                    value="<?php echo htmlspecialchars($user['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full rounded-lg border border-outline px-4 py-3 bg-surface" required />
            </div>
            <div>
                <label class="block text-sm text-on-surface-variant mb-2" for="bio">Biyografi</label>
                <textarea id="bio" name="bio" rows="4" maxlength="140"
                    class="w-full rounded-lg border border-outline px-4 py-3 bg-surface"
                    data-max-chars="140"><?php echo htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                <p id="bio-helper" class="text-xs text-on-surface-variant mt-1">Maksimum 140 karakter</p>
            </div>
            <div class="flex flex-wrap gap-4 mt-4">
                <button type="submit"
                    class="bg-primary text-on-primary px-6 py-3 rounded-lg hover:bg-on-primary-fixed transition-colors">Kaydet</button>
                <a href="profile.php"
                    class="border border-outline text-on-surface px-6 py-3 rounded-lg hover:bg-surface-container transition-colors">Profilime
                    Dön</a>
            </div>
        </form>
    </section>
</main>
<?php include '../includes/footer.php'; ?>

<script>
    const bioInput = document.getElementById('bio');
    const bioHelper = document.getElementById('bio-helper');
    if (bioInput) {
        const maxChars = parseInt(bioInput.dataset.maxChars || '140', 10);
        const updateBioCount = () => {
            let value = bioInput.value || '';
            if (value.length > maxChars) {
                value = value.slice(0, maxChars);
                bioInput.value = value;
            }
            const count = value.length;
            if (bioHelper) {
                bioHelper.textContent = `Maksimum ${maxChars} karakter (${count}/${maxChars})`;
            }
        };
        updateBioCount();
        bioInput.addEventListener('input', updateBioCount);
    }
</script>