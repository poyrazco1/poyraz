<?php
/** Ana navigasyon — gruplu dropdown yapı, mobilde hamburger menü */
$navServices = rows_lang(
    'SELECT title, slug FROM services WHERE lang = ? AND status = 1 ORDER BY sort_order LIMIT 12'
);
$logoUrl = setting('logo') ? upload_url(setting('logo')) : asset('img/logo.svg');
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('')) ?>" aria-label="<?= e(setting('site_name', SITE_NAME)) ?>">
            <img src="<?= e($logoUrl) ?>" alt="<?= e(setting('site_name', SITE_NAME)) ?>" class="brand-logo">
        </a>

        <button class="nav-toggle" aria-label="Menü" aria-expanded="false" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" data-nav aria-label="Ana menü">
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
        </nav>
    </div>
</header>
