<?php
/**
 * Ana navigasyon.
 * Masaüstü: gruplu dropdown menü. Mobil: sağdan kayan off-canvas panel
 * (overlay + accordion gruplar + CTA & sosyal alan).
 */
$navServices = rows_lang(
    'SELECT title, slug FROM services WHERE lang = ? AND status = 1 ORDER BY sort_order LIMIT 12'
);
$logoUrl = setting('logo') ? upload_url(setting('logo')) : asset('img/logo.svg');
$waHref = whatsapp_link(t('whatsapp.default_message'));
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('')) ?>" aria-label="<?= e(setting('site_name', SITE_NAME)) ?>">
            <img src="<?= e($logoUrl) ?>" alt="<?= e(setting('site_name', SITE_NAME)) ?>" class="brand-logo">
        </a>

        <button class="nav-toggle" aria-label="Menü" aria-expanded="false"
                aria-controls="mobileNav" data-nav-toggle>
            <?= icon('menu', 'nav-toggle-icon icon--open', 26) ?>
            <?= icon('close', 'nav-toggle-icon icon--close', 26) ?>
        </button>

        <nav class="main-nav" id="mobileNav" data-nav aria-label="Ana menü">
            <div class="mobile-menu-head">
                <div>
                    <span class="mm-brand">D4<span>s</span>tattoo</span>
                    <span class="mm-tagline"><?= e(setting('location_text', 'İstanbul / Bağcılar')) ?> Tattoo Studio</span>
                </div>
                <button type="button" class="mobile-menu-close" data-nav-close aria-label="<?= e(t('btn.close')) ?>">
                    <?= icon('close', '', 22) ?>
                </button>
            </div>

            <ul class="nav-list">
                <li><a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a></li>
                <li class="has-dropdown">
                    <button type="button" class="drop-btn" aria-expanded="false"><?= e(t('nav.studio')) ?> <span class="caret"></span></button>
                    <ul class="dropdown">
                        <li><a href="<?= e(url('hakkimizda')) ?>"><?= e(t('nav.about')) ?></a></li>
                        <li><a href="<?= e(url('sanatci')) ?>"><?= e(t('nav.artist')) ?></a></li>
                        <li><a href="<?= e(url('hijyen')) ?>"><?= e(t('nav.hygiene')) ?></a></li>
                    </ul>
                </li>
                <li class="has-dropdown">
                    <button type="button" class="drop-btn" aria-expanded="false"><?= e(t('nav.services')) ?> <span class="caret"></span></button>
                    <ul class="dropdown dropdown-wide">
                        <?php foreach ($navServices as $s): ?>
                        <li><a href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e($s['title']) ?></a></li>
                        <?php endforeach; ?>
                        <li class="dropdown-all"><a href="<?= e(url('hizmetler')) ?>"><?= e(t('nav.all_services')) ?> →</a></li>
                    </ul>
                </li>
                <li class="has-dropdown">
                    <button type="button" class="drop-btn" aria-expanded="false"><?= e(t('nav.gallery')) ?> <span class="caret"></span></button>
                    <ul class="dropdown">
                        <li><a href="<?= e(url('galeri')) ?>"><?= e(t('nav.portfolio')) ?></a></li>
                        <li><a href="<?= e(url('once-sonra')) ?>"><?= e(t('nav.before_after')) ?></a></li>
                        <li><a href="<?= e(url('dovme-modelleri')) ?>"><?= e(t('nav.models')) ?></a></li>
                    </ul>
                </li>
                <li class="has-dropdown">
                    <button type="button" class="drop-btn" aria-expanded="false"><?= e(t('nav.info')) ?> <span class="caret"></span></button>
                    <ul class="dropdown">
                        <li><a href="<?= e(url('blog')) ?>"><?= e(t('nav.blog')) ?></a></li>
                        <li><a href="<?= e(url('akademi')) ?>"><?= e(t('nav.academy')) ?></a></li>
                        <li><a href="<?= e(url('bakim-talimatlari')) ?>"><?= e(t('nav.aftercare')) ?></a></li>
                        <li><a href="<?= e(url('sss')) ?>"><?= e(t('nav.faq')) ?></a></li>
                    </ul>
                </li>
                <li><a href="<?= e(url('fiyat-listesi')) ?>"><?= e(t('nav.prices')) ?></a></li>
                <li><a href="<?= e(url('iletisim')) ?>"><?= e(t('nav.contact')) ?></a></li>
                <li class="nav-cta"><a class="btn btn-primary btn-sm" href="<?= e(url('randevu-al')) ?>"><?= e(t('nav.appointment')) ?></a></li>
                <li class="nav-lang"><?php require BASE_PATH . '/app/views/layout/language-switcher.php'; ?></li>
            </ul>

            <div class="mobile-menu-cta">
                <a class="btn btn-wa btn-block" href="<?= e($waHref) ?>" target="_blank" rel="noopener">
                    <?= icon('whatsapp', '', 20) ?> <?= e(t('btn.whatsapp_quote')) ?>
                </a>
                <a class="btn btn-primary btn-block" href="<?= e(url('randevu-al')) ?>">
                    <?= icon('calendar', '', 20) ?> <?= e(t('btn.appointment')) ?>
                </a>
                <div class="mobile-menu-meta">
                    <a href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener">
                        <?= icon('instagram', '', 18) ?> D4stattoo
                    </a>
                    <span><?= icon('clock', '', 18) ?> <?= e(setting('working_hours', t('footer.hours_value'))) ?></span>
                    <span><?= icon('location', '', 18) ?> <?= e(setting('location_text', 'İstanbul / Bağcılar')) ?></span>
                </div>
            </div>
        </nav>
    </div>
</header>
<div class="nav-overlay" data-nav-overlay hidden></div>
