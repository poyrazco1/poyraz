<?php
/** Portfolyo / Galeri — kategori filtreli, lightbox'lı */
$cats = rows_lang('SELECT * FROM gallery_categories WHERE lang = ? AND status = 1 ORDER BY sort_order');
$items = Database::all(
    "SELECT gi.*, gc.slug AS cat_slug, gc.name AS cat_name
     FROM gallery_items gi LEFT JOIN gallery_categories gc ON gc.id = gi.category_id
     WHERE gi.status = 1 AND gi.type = 'image' ORDER BY gi.sort_order"
);
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.gallery'), 'path' => 'galeri'],
];
$meta = [
    'title'       => t('title.gallery') . ' — ' . setting('site_name', SITE_NAME),
    'description' => t('home.gallery_sub') . ' ' . setting('location_text', 'İstanbul / Bağcılar'),
    'canonical'   => 'galeri',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.gallery')) ?></h1>
        <p class="lead"><?= e(t('home.gallery_sub')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="gallery-filters">
            <button type="button" class="filter-btn active" data-filter="all"><?= e(t('gallery.all')) ?></button>
            <?php foreach ($cats as $c): ?>
            <button type="button" class="filter-btn" data-filter="<?= e($c['slug']) ?>"><?= e($c['name']) ?></button>
            <?php endforeach; ?>
        </div>
        <?php if ($items): ?>
        <div class="gallery-grid">
            <?php foreach ($items as $g): ?>
            <button type="button" class="gallery-item" data-cat="<?= e($g['cat_slug'] ?? '') ?>"
                    data-lightbox="<?= e(upload_url($g['image'])) ?>" data-caption="<?= e($g['title']) ?>">
                <img src="<?= e(upload_url($g['image'])) ?>" alt="<?= e($g['alt_text'] ?: $g['title']) ?>" loading="lazy">
                <span class="gi-label"><?= e($g['title']) ?><?= $g['cat_name'] ? ' — ' . e($g['cat_name']) : '' ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="gallery-empty"><?= e(t('gallery.empty')) ?></p>
        <?php endif; ?>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('once-sonra')) ?>"><?= e(t('nav.before_after')) ?></a>
            <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
        </div>
    </div>
</section>
<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="<?= e(t('btn.close')) ?>">×</button>
    <img src="" alt="">
    <div class="lightbox-caption"></div>
</div>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
