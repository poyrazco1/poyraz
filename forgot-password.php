<?php

declare(strict_types=1);

/**
 * Şifremi unuttum.
 *
 * Kullanıcı adı veya e-posta alınır. Kullanıcı varsa password_resets tablosuna
 * süreli bir token yazılır. SMTP henüz kurulmadığından, geliştirme modunda
 * (DEBUG=true) sıfırlama bağlantısı ekranda gösterilir. Üretimde kullanıcı
 * sıralaması (enumeration) sızmasın diye her durumda genel mesaj verilir.
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

/** Token geçerlilik süresi (dakika). */
const RESET_TOKEN_MINUTES = 60;

$message   = '';
$devLink   = '';
$isError   = false;
$submitted = false;

if (is_post()) {
    $submitted = true;
    $token = $_POST['_csrf'] ?? null;

    if (!csrf_verify(is_string($token) ? $token : null)) {
        $isError = true;
        $message = 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
    } else {
        $identifier = input('identifier');

        if ($identifier === '') {
            $isError = true;
            $message = 'Lütfen kullanıcı adı veya e-posta girin.';
        } else {
            $stmt = db()->prepare('SELECT id FROM users WHERE (username = :id OR email = :id) AND status = 1 LIMIT 1');
            $stmt->execute([':id' => $identifier]);
            $user = $stmt->fetch();

            if ($user !== false) {
                $resetToken = bin2hex(random_bytes(32));
                $expiresAt  = date('Y-m-d H:i:s', time() + RESET_TOKEN_MINUTES * 60);

                $ins = db()->prepare(
                    'INSERT INTO password_resets (user_id, token, expires_at, created_at)
                     VALUES (:uid, :token, :exp, NOW())'
                );
                $ins->execute([
                    ':uid'   => (int) $user['id'],
                    ':token' => $resetToken,
                    ':exp'   => $expiresAt,
                ]);

                // Geliştirme modunda bağlantıyı göster (SMTP eklenince mail atılır).
                if (DEBUG) {
                    $devLink = url('reset-password.php?token=' . $resetToken);
                }
            }

            // Her durumda genel mesaj (kullanıcı sıralaması engellenir).
            $message = 'Hesabınız bulunduysa, şifre sıfırlama bağlantısı oluşturulmuştur. '
                . 'Lütfen e-posta kutunuzu kontrol edin.';
        }
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Şifremi Unuttum · <?= e(SITE_NAME) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= e(url('assets/css/auth.css')) ?>">
</head>
<body class="auth">
    <div class="auth__wrap">
        <div class="auth__card">
            <div class="auth__brand">
                <span class="auth__logo">P</span>
                <span class="auth__brand-text"><?= e(SITE_NAME) ?></span>
            </div>

            <h1 class="auth__title">Şifremi Unuttum</h1>
            <p class="auth__subtitle">Hesabınıza bağlı kullanıcı adı veya e-postayı girin.</p>

            <?php if ($message !== ''): ?>
                <div class="alert alert--<?= $isError ? 'error' : 'success' ?>" role="alert"><?= e($message) ?></div>
            <?php endif; ?>

            <?php if ($devLink !== ''): ?>
                <div class="alert alert--info" role="status">
                    <strong>Geliştirme bağlantısı:</strong><br>
                    <a class="auth__link" href="<?= e($devLink) ?>"><?= e($devLink) ?></a>
                </div>
            <?php endif; ?>

            <?php if (!$submitted || $isError): ?>
            <form method="post" action="<?= e(url('forgot-password.php')) ?>" class="auth__form" novalidate>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="identifier">Kullanıcı adı veya e-posta</label>
                    <input type="text" id="identifier" name="identifier" class="form-control"
                           autocomplete="username" autofocus required>
                </div>
                <button type="submit" class="btn btn--primary btn--block">Sıfırlama Bağlantısı Oluştur</button>
            </form>
            <?php endif; ?>
        </div>

        <p class="auth__foot">
            <a class="auth__link" href="<?= e(url('login.php')) ?>">&larr; Giriş sayfasına dön</a>
        </p>
    </div>

    <script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
