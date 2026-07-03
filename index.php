<?php

declare(strict_types=1);

/**
 * Public tek sayfa (landing).
 *
 * Sade, kurumsal bir tanıtım sayfası. Tek eylem: "Giriş Yap".
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

// Oturum açıksa doğrudan panele al.
if (is_logged_in()) {
    redirect('/dashboard.php');
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(SITE_NAME) ?></title>
    <meta name="description" content="Kurumsal şirket yönetim paneli — kullanıcı, rol ve temel operasyon yönetimi.">
    <link rel="stylesheet" href="<?= e(url('assets/css/auth.css')) ?>">
</head>
<body class="landing">
    <header class="landing__header">
        <div class="landing__container landing__header-inner">
            <div class="landing__brand">
                <span class="landing__logo">P</span>
                <span class="landing__brand-text"><?= e(SITE_NAME) ?></span>
            </div>
            <a class="btn btn--primary" href="<?= e(url('login.php')) ?>">Giriş Yap</a>
        </div>
    </header>

    <main class="landing__hero">
        <div class="landing__container landing__hero-inner">
            <div class="landing__hero-copy">
                <span class="landing__eyebrow">Kurumsal Yönetim Paneli</span>
                <h1 class="landing__title">Şirketinizi tek panelden, sade ve düzenli yönetin.</h1>
                <p class="landing__lead">
                    Kullanıcılar, roller ve temel operasyonlar için hazır bir altyapı.
                    Güvenli giriş, yetki yönetimi ve genişletilebilir modüler mimari
                    ile ekibiniz için sağlam bir başlangıç noktası.
                </p>
                <div class="landing__actions">
                    <a class="btn btn--primary btn--lg" href="<?= e(url('login.php')) ?>">Giriş Yap</a>
                </div>
            </div>

            <div class="landing__hero-panel" aria-hidden="true">
                <div class="landing__card">
                    <div class="landing__card-row">
                        <span class="landing__dot"></span>
                        <span class="landing__dot"></span>
                        <span class="landing__dot"></span>
                    </div>
                    <div class="landing__card-line landing__card-line--wide"></div>
                    <div class="landing__card-grid">
                        <div class="landing__card-tile"></div>
                        <div class="landing__card-tile"></div>
                        <div class="landing__card-tile"></div>
                        <div class="landing__card-tile"></div>
                    </div>
                    <div class="landing__card-line"></div>
                    <div class="landing__card-line landing__card-line--short"></div>
                </div>
            </div>
        </div>
    </main>

    <section class="landing__features">
        <div class="landing__container landing__features-grid">
            <div class="landing__feature">
                <h3>Güvenli Giriş</h3>
                <p>CSRF koruması, güvenli şifre saklama ve “beni hatırla” desteğiyle oturum yönetimi.</p>
            </div>
            <div class="landing__feature">
                <h3>Rol & Yetki</h3>
                <p>Kullanıcı ve rol yönetimi; ileride modül bazlı yetkilere genişletilebilir yapı.</p>
            </div>
            <div class="landing__feature">
                <h3>Modüler Mimari</h3>
                <p>Her modül kendi klasöründe. Yeni araç ve ekranları temiz bir düzende ekleyin.</p>
            </div>
        </div>
    </section>

    <footer class="landing__footer">
        <div class="landing__container">
            <span>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?> · Tüm hakları saklıdır.</span>
        </div>
    </footer>
</body>
</html>
