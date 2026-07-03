<?php

declare(strict_types=1);

/**
 * Şifre sıfırlama.
 *
 * Geçerli (kullanılmamış ve süresi dolmamış) bir token ile yeni şifre belirlenir.
 * Token tek kullanımlıktır; kullanıldığında used_at doldurulur.
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

$token = input('token');
$error = '';
$fatal = '';

/**
 * Verilen token için geçerli sıfırlama kaydını döndürür; yoksa null.
 *
 * @return array<string, mixed>|null
 */
function find_valid_reset(string $token): ?array
{
    if ($token === '') {
        return null;
    }
    $stmt = db()->prepare(
        'SELECT * FROM password_resets
         WHERE token = :t AND used_at IS NULL AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([':t' => $token]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

$reset = find_valid_reset($token);

if ($reset === null) {
    $fatal = 'Sıfırlama bağlantısı geçersiz veya süresi dolmuş. Lütfen yeni bir bağlantı isteyin.';
}

if ($fatal === '' && is_post()) {
    $csrf = $_POST['_csrf'] ?? null;
    if (!csrf_verify(is_string($csrf) ? $csrf : null)) {
        $error = 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        if (strlen($password) < 8) {
            $error = 'Şifre en az 8 karakter olmalıdır.';
        } elseif ($password !== $confirm) {
            $error = 'Şifreler eşleşmiyor.';
        } else {
            // Token'ı işlem içinde tekrar doğrula (yarış durumu koruması).
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $check = $pdo->prepare(
                    'SELECT * FROM password_resets
                     WHERE id = :id AND used_at IS NULL AND expires_at > NOW()
                     FOR UPDATE'
                );
                $check->execute([':id' => (int) $reset['id']]);
                $fresh = $check->fetch();

                if ($fresh === false) {
                    $pdo->rollBack();
                    $fatal = 'Sıfırlama bağlantısı geçersiz veya süresi dolmuş.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $upd = $pdo->prepare('UPDATE users SET password_hash = :h, remember_token = NULL WHERE id = :uid');
                    $upd->execute([':h' => $hash, ':uid' => (int) $fresh['user_id']]);

                    $mark = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
                    $mark->execute([':id' => (int) $fresh['id']]);

                    // Aynı kullanıcının diğer bekleyen token'larını da geçersiz kıl.
                    $inv = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = :uid AND used_at IS NULL');
                    $inv->execute([':uid' => (int) $fresh['user_id']]);

                    $pdo->commit();

                    flash('success', 'Şifreniz güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
                    redirect('/login.php');
                }
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $ex;
            }
        }
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Şifre Belirle · <?= e(SITE_NAME) ?></title>
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

            <h1 class="auth__title">Yeni Şifre Belirle</h1>

            <?php if ($fatal !== ''): ?>
                <div class="alert alert--error" role="alert"><?= e($fatal) ?></div>
                <p class="auth__foot">
                    <a class="auth__link" href="<?= e(url('forgot-password.php')) ?>">Yeni bağlantı iste</a>
                </p>
            <?php else: ?>
                <p class="auth__subtitle">Hesabınız için yeni bir şifre belirleyin.</p>

                <?php if ($error !== ''): ?>
                    <div class="alert alert--error" role="alert"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(url('reset-password.php')) ?>" class="auth__form" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <div class="form-group">
                        <label for="password">Yeni şifre</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-control"
                                   autocomplete="new-password" minlength="8" required>
                            <button type="button" class="password-toggle" data-password-toggle="password"
                                    aria-label="Şifreyi göster/gizle">
                                <svg class="password-toggle__eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8zm0-2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                            </button>
                        </div>
                        <small class="form-hint">En az 8 karakter.</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Yeni şifre (tekrar)</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                               autocomplete="new-password" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">Şifreyi Güncelle</button>
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
