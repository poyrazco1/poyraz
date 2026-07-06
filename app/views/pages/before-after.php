<?php
/** Önce / Sonra — cover-up ve yenileme çalışmaları */
$pairs = Database::all(
    "SELECT * FROM gallery_items WHERE status = 1 AND type = 'before_after' ORDER BY sort_order"
);
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.before_after'), 'path' => 'once-sonra'],
];
$meta = [
    'title'       => t('title.before_after') . ' — Cover-up & ' . t('nav.services'),
    'description' => t('title.before_after') . ': ' . setting('site_description'),
    'canonical'   => 'once-sonra',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.before_after')) ?></h1>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($pairs): ?>
        <div class="grid grid-2">
            <?php foreach ($pairs as $p): ?>
            <div>
                <div class="ba-pair">
                    <div class="ba-half">
                        <span class="ba-tag"><?= e(t('gallery.before')) ?></span>
                        <img src="<?= e(upload_url($p['before_image'])) ?>" alt="<?= e($p['alt_text'] ?: $p['title']) ?> — <?= e(t('gallery.before')) ?>" loading="lazy">
                    </div>
                    <div class="ba-half">
                        <span class="ba-tag after"><?= e(t('gallery.after')) ?></span>
                        <img src="<?= e(upload_url($p['after_image'])) ?>" alt="<?= e($p['alt_text'] ?: $p['title']) ?> — <?= e(t('gallery.after')) ?>" loading="lazy">
                    </div>
                </div>
                <p class="ba-title"><?= e($p['title']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="gallery-empty"><?= e(t('gallery.empty')) ?></p>
        <?php endif; ?>
        <div class="cta-band" style="margin-top:48px">
            <div>
                <h2><?= e(t('service.cta_title')) ?></h2>
                <p><?= e(t('service.cta_text')) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-primary" href="<?= e(url('hizmetler/cover-up')) ?>">Cover-up</a>
                <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp_quote')) ?></a>
            </div>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
