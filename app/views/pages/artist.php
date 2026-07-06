<?php
/** Sanatçı tanıtım sayfası — içerik site ayarlarından yönetilir */
$works = Database::all(
    "SELECT * FROM gallery_items WHERE status = 1 AND type = 'image' ORDER BY sort_order LIMIT 6"
);
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.artist'), 'path' => 'sanatci'],
];
$meta = [
    'title'       => t('title.artist') . ' — ' . setting('artist_name', 'D4stattoo Artist'),
    'description' => setting('artist_bio'),
    'canonical'   => 'sanatci',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(setting('artist_name', 'D4stattoo Artist')) ?></h1>
        <p class="lead"><?= e(setting('site_slogan')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container artist-wrap">
        <div class="artist-photo">
            <img src="<?= e(upload_url(setting('artist_image'), 'img/demo/artist.svg')) ?>" alt="<?= e(setting('artist_name', 'D4stattoo Artist')) ?>">
        </div>
        <div class="artist-info">
            <h2><?= e(t('home.artist_title')) ?></h2>
            <p><?= e(setting('artist_bio')) ?></p>
            <ul class="artist-tags">
                <li><?= e(t('badge.custom')) ?></li>
                <li><?= e(t('badge.consult')) ?></li>
                <li><?= e(t('badge.single_needle')) ?></li>
                <li><?= e(t('badge.experience', ['years' => setting('experience_years', '3')])) ?></li>
            </ul>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
                <a class="btn btn-outline" href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener"><?= e(t('btn.instagram')) ?></a>
            </div>
        </div>
    </div>
</section>
<?php if ($works): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2><?= e(t('home.gallery_title')) ?></h2>
        </div>
        <div class="gallery-grid">
            <?php foreach ($works as $g): ?>
            <button type="button" class="gallery-item"
                    data-lightbox="<?= e(media_url($g['image'], gallery_fallback_image())) ?>" data-caption="<?= e($g['title']) ?>">
                <img src="<?= e(media_url($g['image'], gallery_fallback_image())) ?>" alt="<?= e($g['alt_text'] ?: $g['title']) ?>" loading="lazy">
                <span class="gi-label"><?= e($g['title']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('galeri')) ?>"><?= e(t('btn.view_gallery')) ?></a>
        </div>
    </div>
</section>
<?php endif; ?>
<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="<?= e(t('btn.close')) ?>">×</button>
    <img src="" alt="">
    <div class="lightbox-caption"></div>
</div>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
