<?php
/** Dövme Modelleri — hazır model/eskiz galerisi */
$cats = rows_lang('SELECT * FROM gallery_categories WHERE lang = ? AND status = 1 ORDER BY sort_order');
$items = Database::all(
    "SELECT gi.*, gc.slug AS cat_slug, gc.name AS cat_name
     FROM gallery_items gi LEFT JOIN gallery_categories gc ON gc.id = gi.category_id
     WHERE gi.status = 1 AND gi.type = 'model' ORDER BY gi.sort_order"
);
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.models'), 'path' => 'dovme-modelleri'],
];
$meta = [
    'title'       => t('title.models') . ' — ' . setting('site_name', SITE_NAME),
    'description' => t('title.models') . ' — ' . setting('site_description'),
    'canonical'   => 'dovme-modelleri',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.models')) ?></h1>
        <p class="lead"><?= e(t('badge.custom')) ?> — <?= e(t('service.cta_text')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($items): ?>
        <div class="gallery-grid">
            <?php foreach ($items as $g): ?>
            <button type="button" class="gallery-item" data-cat="<?= e($g['cat_slug'] ?? '') ?>"
                    data-lightbox="<?= e(upload_url($g['image'])) ?>" data-caption="<?= e($g['title']) ?>">
                <img src="<?= e(upload_url($g['image'])) ?>" alt="<?= e($g['alt_text'] ?: $g['title']) ?>" loading="lazy">
                <span class="gi-label"><?= e($g['title']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="gallery-empty"><?= e(t('gallery.empty')) ?></p>
        <?php endif; ?>
        <div class="cta-band" style="margin-top:48px">
            <div>
                <h2><?= e(t('home.quote_cta_title')) ?></h2>
                <p><?= e(t('home.quote_cta_text')) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-primary" href="<?= e(url('fiyat-teklifi-al')) ?>"><?= e(t('btn.quote')) ?></a>
            </div>
        </div>
    </div>
</section>
<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="<?= e(t('btn.close')) ?>">×</button>
    <img src="" alt="">
    <div class="lightbox-caption"></div>
</div>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
