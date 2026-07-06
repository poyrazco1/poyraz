<?php
/** Fiyat listesi */
$prices = rows_lang('SELECT * FROM price_list_items WHERE lang = ? AND status = 1 ORDER BY sort_order');
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.prices'), 'path' => 'fiyat-listesi'],
];
$meta = [
    'title'       => t('title.prices') . ' — Dövme Fiyatları ' . date('Y'),
    'description' => t('prices.note') . ' ' . setting('location_text', 'İstanbul / Bağcılar'),
    'canonical'   => 'fiyat-listesi',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.prices')) ?></h1>
        <p class="lead"><?= e(t('prices.note')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="price-list">
            <?php foreach ($prices as $p): ?>
            <div class="price-item">
                <div>
                    <h3><?= e($p['title']) ?></h3>
                    <p class="price-desc"><?= e($p['description']) ?></p>
                </div>
                <span class="price-tag"><?= e($p['price_text']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="price-note"><?= e(t('prices.note')) ?></p>
        <div class="cta-band" style="margin-top:44px">
            <div>
                <h2><?= e(t('home.quote_cta_title')) ?></h2>
                <p><?= e(t('prices.cta')) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-primary" href="<?= e(url('fiyat-teklifi-al')) ?>"><?= e(t('btn.quote')) ?></a>
                <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp_quote')) ?></a>
            </div>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
