<?php
/** Hizmet detay — her hizmet için ayrı SEO sayfası */
$service = row_lang('SELECT * FROM services WHERE lang = ? AND slug = ? AND status = 1 LIMIT 1', [$slug]);
if (!$service) {
    http_response_code(404);
    require BASE_PATH . '/app/views/pages/404.php';
    return;
}

$otherServices = rows_lang(
    'SELECT title, slug FROM services WHERE lang = ? AND status = 1 AND slug != ? ORDER BY sort_order',
    [$service['slug']]
);
$faqs = rows_lang('SELECT * FROM faq_items WHERE lang = ? AND status = 1 ORDER BY sort_order LIMIT 4');
$galleryPreview = Database::all(
    "SELECT * FROM gallery_items WHERE status = 1 AND type = 'image' ORDER BY sort_order LIMIT 4"
);

$waMessage = str_replace(
    [':style'],
    [$service['title']],
    t('whatsapp.default_message') . ' Stil: ' . $service['title']
);

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.services'), 'path' => 'hizmetler'],
    ['name' => $service['title'], 'path' => 'hizmetler/' . $service['slug']],
];
$meta = [
    'title'       => $service['meta_title'] ?: $service['title'],
    'description' => $service['meta_description'] ?: $service['short_description'],
    'canonical'   => 'hizmetler/' . $service['slug'],
    'image'       => $service['image'],
];
$extraHead = jsonld_breadcrumb($breadcrumbs) . jsonld_faq($faqs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e($service['title']) ?></h1>
        <p class="lead"><?= e($service['short_description']) ?></p>
        <div class="hero-actions" style="margin-top:22px">
            <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
            <a class="btn btn-wa" href="<?= e(whatsapp_link($waMessage)) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp_quote')) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container detail-layout">
        <div>
            <div class="post-cover">
                <img src="<?= e(media_url($service['image'], service_demo_image($service['slug']))) ?>" alt="<?= e($service['title']) ?>">
            </div>
            <div class="prose"><?= clean_html($service['content'] ?? '') ?></div>

            <?php if ($galleryPreview): ?>
            <h2 style="margin-top:44px"><?= e(t('home.gallery_title')) ?></h2>
            <p style="color:var(--muted)"><?= e(t('service.gallery_link')) ?></p>
            <div class="gallery-grid" style="grid-template-columns:repeat(4,1fr)">
                <?php foreach ($galleryPreview as $g): ?>
                <button type="button" class="gallery-item"
                        data-lightbox="<?= e(media_url($g['image'], gallery_fallback_image())) ?>" data-caption="<?= e($g['title']) ?>">
                    <img src="<?= e(media_url($g['image'], gallery_fallback_image())) ?>" alt="<?= e($g['alt_text'] ?: $g['title']) ?>" loading="lazy">
                </button>
                <?php endforeach; ?>
            </div>
            <div class="section-more" style="text-align:start">
                <a class="btn btn-outline" href="<?= e(url('galeri')) ?>"><?= e(t('btn.view_gallery')) ?></a>
            </div>
            <?php endif; ?>

            <?php if ($faqs): ?>
            <h2 style="margin-top:44px"><?= e(t('service.faq')) ?></h2>
            <div class="faq-list" style="max-width:none">
                <?php foreach ($faqs as $f): ?>
                <div class="faq-item">
                    <button type="button" class="faq-q"><?= e($f['question']) ?></button>
                    <div class="faq-a"><div class="faq-a-inner"><?= e($f['answer']) ?></div></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <aside class="sidebar">
            <div class="side-card side-cta">
                <h3><?= e(t('service.cta_title')) ?></h3>
                <p><?= e(t('service.cta_text')) ?></p>
                <a class="btn btn-primary btn-block" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
                <a class="btn btn-wa btn-block" style="margin-top:10px" href="<?= e(whatsapp_link($waMessage)) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp_quote')) ?></a>
                <a class="btn btn-outline btn-block" style="margin-top:10px" href="<?= e(url('fiyat-teklifi-al')) ?>"><?= e(t('btn.quote')) ?></a>
            </div>
            <div class="side-card">
                <h3><?= e(t('nav.all_services')) ?></h3>
                <ul>
                    <?php foreach ($otherServices as $o): ?>
                    <li><a href="<?= e(url('hizmetler/' . $o['slug'])) ?>"><?= e($o['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
    </div>
</section>
<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="<?= e(t('btn.close')) ?>">×</button>
    <img src="" alt="">
    <div class="lightbox-caption"></div>
</div>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
