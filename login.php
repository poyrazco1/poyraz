<?php

declare(strict_types=1);

/**
 * Giriş sayfası.
 *
 * Kullanıcı adı veya e-posta + şifre ile giriş. CSRF korumalı, "beni hatırla"
 * ve "şifremi unuttum" bağlantılı. Oturum açıksa dashboard'a yönlendirir.
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

$error      = '';
$identifier = '';

if (is_post()) {
    $token = $_POST['_csrf'] ?? null;
    if (!csrf_verify(is_string($token) ? $token : null)) {
        $error = 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
    } else {
        $identifier = input('identifier');
        $password   = $_POST['password'] ?? '';
        $remember   = isset($_POST['remember']);

        $result = attempt_login($identifier, is_string($password) ? $password : '', $remember);
        if ($result['success']) {
            redirect('/dashboard.php');
        }
        $error = $result['message'];
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap · <?= e(SITE_NAME) ?></title>
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

            <h1 class="auth__title">Giriş Yap</h1>
            <p class="auth__subtitle">Devam etmek için hesabınıza giriş yapın.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert--error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('login.php')) ?>" class="auth__form" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="identifier">Kullanıcı adı veya e-posta</label>
                    <input type="text" id="identifier" name="identifier" class="form-control"
                           value="<?= e($identifier) ?>" autocomplete="username"
                           autofocus required>
                </div>

                <div class="form-group">
                    <label for="password">Şifre</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control"
                               autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-password-toggle="password"
                                aria-label="Şifreyi göster/gizle">
                            <svg class="password-toggle__eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8zm0-2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="auth__row">
                    <label class="checkbox">
                        <input type="checkbox" name="remember" value="1">
                        <span>Beni hatırla</span>
                    </label>
                    <a class="auth__link" href="<?= e(url('forgot-password.php')) ?>">Şifremi unuttum</a>
                </div>

                <button type="submit" class="btn btn--primary btn--block">Giriş Yap</button>
            </form>
        </div>

        <p class="auth__foot">
            <a class="auth__link" href="<?= e(url('index.php')) ?>">&larr; Ana sayfaya dön</a>
        </p>
    </div>

    <script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
